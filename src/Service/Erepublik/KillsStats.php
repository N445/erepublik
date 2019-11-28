<?php

namespace App\Service\Erepublik;

use App\Clients\Erepublik;
use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile;
use App\Entity\Profile\Profile as ProfileEntity;
use App\Entity\Profile\UniteMilitaire;
use App\Repository\KillsStats\PlaneRepository;
use App\Repository\Profile\ProfileRepository;
use App\Repository\Profile\UniteMilitaireRepository;
use App\Utils\MondayHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class KillsStats
{
    /**
     * @var Erepublik
     */
    private $erepublikClient;

    /**
     * @var array
     */
    private $profiles = [];

    /**
     * @var array
     */
    private $profilesEntities = [];

    /**
     * @var array
     */
    private $umIds = [];

    /**
     * @var array
     */
    private $umEntities = [];

    /**
     * @var CookieJar
     */
    private $cookie;

    /**
     * @var integer
     */
    private $semaine = 0;

    /**
     * @var FilesystemAdapter
     */
    private $cache;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var UniteMilitaireRepository
     */
    private $militaireRepository;

    /**
     * @var PlaneRepository
     */
    private $planeRepository;

    /**
     * KillsStats constructor.
     * @param Erepublik                $erepublikClient
     * @param EntityManagerInterface   $em
     * @param ProfileRepository        $profileRepository
     * @param UniteMilitaireRepository $militaireRepository
     * @param PlaneRepository          $planeRepository
     */
    public function __construct(
        Erepublik $erepublikClient,
        EntityManagerInterface $em,
        ProfileRepository $profileRepository,
        UniteMilitaireRepository $militaireRepository,
        PlaneRepository $planeRepository
    )
    {
        $this->erepublikClient     = $erepublikClient;
        $this->cache               = new FilesystemAdapter();
        $this->em                  = $em;
        $this->profileRepository   = $profileRepository;
        $this->militaireRepository = $militaireRepository;
        $this->planeRepository     = $planeRepository;
    }

    /**
     * @param string          $cookieValue
     * @param string          $semaine
     * @param ProfileEntity[] $profileData
     * @return array|\Exception
     * @throws \Exception
     */
    public function run(string $cookieValue, string $semaine, array $profileData)
    {
        $this->initData($cookieValue, $semaine, $profileData);
        if (!$this->cookie) {
            return new \Exception("Cookie not set");
        }
        if (empty($this->profiles) || empty($this->umIds)) {
            return [];
        }
        $this->browseLeaderBoards();
        $this->em->flush();
        return $this->profiles;
    }

    /**
     * @param int $semaine
     * @return KillsStats
     */
    public function setSemaine(int $semaine): KillsStats
    {
        $this->semaine = $semaine;
        return $this;
    }

    /**
     * @param array $profiles
     * @return KillsStats
     */
    public function setProfilesAndUmIds(array $profiles): KillsStats
    {
        /** @var ProfileEntity $profile */
        foreach ($profiles as $profile) {
            $profile = $this->getProfile($profile);

            $this->profiles[$profile->getIdentifier()]                   = $profile;
            $this->umIds[$profile->getUnitemilitaire()->getIdentifier()] = $profile->getUnitemilitaire()->getName();
        }
        return $this;
    }

    /**
     * @param $value
     * @return KillsStats
     */
    public function setCookie($value): KillsStats
    {
        $setCookie = new SetCookie();
        $setCookie->setPath('/');
        $setCookie->setDomain('.erepublik.com');
        $setCookie->setName('erpk');
        $setCookie->setValue($value);
        $this->cookie = new CookieJar();
        $this->cookie->setCookie($setCookie);
        return $this;
    }

    /**
     * @throws \Exception
     */
    private function browseLeaderBoards()
    {
        foreach ($this->getLeaderboards() as $leaderboard) {
            $this->setProfileKills($leaderboard);
        }
    }

    /**
     * @return \Generator
     */
    private function getLeaderboards()
    {
        foreach ($this->umIds as $umId => $umName) {
            $url      = sprintf('/fr/main/leaderboards-kills-aircraft-rankings/11/%d/%s/0', $this->semaine, $umId);
            $response = $this->erepublikClient->get($url, [
                'cookies' => $this->cookie,
            ]);
            $data     = json_decode($response->getBody()->getContents());
            if ("application/json" !== explode(';', $response->getHeader("Content-Type")[0])[0]) {
                yield [];
                continue;
            }

            yield $data->top;
        }
    }

    /**
     * @param $scores
     * @throws \Exception
     */
    private function setProfileKills($scores)
    {
        foreach ($scores as $score) {
            if (array_key_exists($score->id, $this->profiles)) {
                /** @var ProfileEntity $profile */
                $profile = $this->profiles[$score->id];
                $this->getStatPlane($profile, $score);
            }
        }
    }

    /**
     * @param ProfileEntity $profile
     * @return ProfileEntity
     */
    private function getProfile(ProfileEntity $profile)
    {
        $profileData = json_decode(
            $this->erepublikClient
                ->get(
                    sprintf('/fr/main/citizen-profile-json/%s'
                        , $profile->getIdentifier()
                    )
                )
                ->getBody()
                ->getContents()
        );
        dump($profile, $profileData);
//        die;

        if (array_key_exists($profile->getIdentifier(), $this->profilesEntities)) {
            $this->profilesEntities[$profile->getIdentifier()]->setIsAlive($profileData->citizen->is_alive);
            return $this->profilesEntities[$profile->getIdentifier()];
        }

        $profile->setName($profile->getName() ? $profile->getName() : $profileData->citizen->name)
                ->setIsAlive($profileData->citizen->is_alive)
        ;

        $profile->setUnitemilitaire(
            $this->getUniteMilitaire(
                $profileData->military->militaryUnit
            )
        );

        $this->em->persist($profile);

        $this->profilesEntities[$profile->getIdentifier()] = $profile;

        return $profile;
    }

    /**
     * @param $dataUniteMilitaire
     * @return UniteMilitaire|mixed|null
     */
    private function getUniteMilitaire($dataUniteMilitaire)
    {
        $identifier = $dataUniteMilitaire->id;
        $name       = $dataUniteMilitaire->name;

        if (array_key_exists($identifier, $this->umEntities)) {
            return $this->umEntities[$identifier];
        }

        $um = new UniteMilitaire();

        $um->setIdentifier($identifier)
           ->setName(trim($name))
        ;

        $this->umEntities[$um->getIdentifier()] = $um;

        return $um;
    }

    /**
     * @param string          $cookieValue
     * @param string          $semaine
     * @param ProfileEntity[] $profileData
     */
    private function initData(string $cookieValue, string $semaine, array $profileData)
    {
        $this->setCookie($cookieValue);
        $this->semaine = MondayHelper::getErepublikSemaine($semaine);
        array_map(function (ProfileEntity $profile) {
            $this->profilesEntities[$profile->getIdentifier()] = $profile;
        }, $this->profileRepository->findAll());

        array_map(function (UniteMilitaire $uniteMilitaire) {
            $this->umEntities[$uniteMilitaire->getIdentifier()] = $uniteMilitaire;
        }, $this->militaireRepository->findAll());

        $this->setProfilesAndUmIds($profileData);
    }

    /**
     * @param ProfileEntity $profile
     * @param               $score
     * @return void
     * @throws NonUniqueResultException
     */
    private function getStatPlane(Profile &$profile, $score)
    {
        if (!$profile->getIsAlive()) {
            return;
        }
        $statsDate = (new \DateTime())->setTimestamp(strtotime('previous monday', (new \DateTime("NOW"))->getTimestamp()));
        if ($this->semaine == 0) {
            $statsDate = (new \DateTime())->setTimestamp(strtotime('next monday', (new \DateTime("NOW"))->getTimestamp()));
        }
        /** @var Plane $planeStat */
        if ($profile->getId() && $planeStat = $this->planeRepository->getPlaneByDate($profile, $statsDate)) {
            $planeStat->setKills($score->values)
                      ->setMoney()
            ;
            return;
        }
        $planeStat = new Plane();
        $planeStat->setKills($score->values)
                  ->setMoney()
                  ->setDate($statsDate)
        ;
        $profile->addPlane($planeStat);
        return;
    }
}