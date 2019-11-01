<?php

namespace App\Service\Erepublik;

use App\Clients\Erepublik;
use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile as ProfileEntity;
use App\Entity\Profile\UniteMilitaire;
use App\Repository\Profile\ProfileRepository;
use App\Repository\Profile\UniteMilitaireRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * KillsStats constructor.
     * @param Erepublik                $erepublikClient
     * @param EntityManagerInterface   $em
     * @param ProfileRepository        $profileRepository
     * @param UniteMilitaireRepository $militaireRepository
     */
    public function __construct(Erepublik $erepublikClient, EntityManagerInterface $em, ProfileRepository $profileRepository, UniteMilitaireRepository $militaireRepository)
    {
        $this->erepublikClient     = $erepublikClient;
        $this->cache               = new FilesystemAdapter();
        $this->em                  = $em;
        $this->profileRepository   = $profileRepository;
        $this->militaireRepository = $militaireRepository;
    }

    /**
     * @param string          $cookieValue
     * @param int             $semaine
     * @param ProfileEntity[] $profileData
     * @return array|\Exception
     * @throws \Exception
     */
    public function run(string $cookieValue, int $semaine, array $profileData)
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
                $profile->addPlane($this->getStatPlane($score));
            }
        }
    }

    /**
     * @param ProfileEntity $profile
     * @return ProfileEntity
     */
    private function getProfile(ProfileEntity $profile)
    {
        if (array_key_exists($profile->getIdentifier(), $this->profilesEntities)) {
            return $this->profilesEntities[$profile->getIdentifier()];
        }

        $response    = $this->erepublikClient->get(sprintf('/fr/main/citizen-profile-json/%s', $profile->getIdentifier()));
        $json        = $response->getBody()->getContents();
        $profileData = json_decode($json);
        $profile->setName($profile->getName() ? $profile->getName() : $profileData->citizen->name);
        if ($profileData->isBanned) {
            $profile->setValid(false);
            return $profile;
        }

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
           ->setName($name)
        ;

        $this->umEntities[$um->getIdentifier()] = $um;

        return $um;
    }

    /**
     * @param string          $cookieValue
     * @param int             $semaine
     * @param ProfileEntity[] $profileData
     */
    private function initData(string $cookieValue, int $semaine, array $profileData)
    {
        $this->setCookie($cookieValue);
        $this->semaine = $semaine;
        $this->setProfilesAndUmIds($profileData);
        array_map(function (ProfileEntity $profile) {
            $this->profilesEntities[$profile->getIdentifier()] = $profile;
        }, $this->profileRepository->findAll());

        array_map(function (UniteMilitaire $uniteMilitaire) {
            $this->umEntities[$uniteMilitaire->getIdentifier()] = $uniteMilitaire;
        }, $this->militaireRepository->findAll());
    }

    /**
     * @param $score
     * @return Plane
     * @throws \Exception
     */
    private function getStatPlane($score)
    {
        $planeStat = new Plane();
        $planeStat->setKills($score->values)->setMoney();
        return $planeStat;
    }
}