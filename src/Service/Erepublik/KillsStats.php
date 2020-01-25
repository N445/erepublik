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
use App\Utils\ProfileHelper;
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
    private $profilesEntities = [];

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
    public function run(string $cookieValue, string $semaine)
    {
        $this->initData($cookieValue, $semaine);
        if (!$this->cookie) {
            return new \Exception("Cookie not set");
        }
        $this->browseLeaderBoards();
        $this->em->flush();
        return $this->profilesEntities;
    }

    /**
     * @param string $cookieValue
     * @param string $semaine
     */
    private function initData(string $cookieValue, string $semaine)
    {
        $this->setCookie($cookieValue);
        $this->semaine = MondayHelper::getErepublikSemaine($semaine);
        array_map(function (ProfileEntity $profile) {
            $this->profilesEntities[$profile->getIdentifier()] = $profile;
        }, $this->profileRepository->findBy([
            'status' => ProfileHelper::ACTIVE,
        ]));

        array_map(function (UniteMilitaire $uniteMilitaire) {
            $this->umEntities[$uniteMilitaire->getIdentifier()] = $uniteMilitaire;
        }, $this->militaireRepository->findAll());
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
        foreach ($this->umEntities as $umId => $umName) {
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
            if (array_key_exists($score->id, $this->profilesEntities)) {
                /** @var ProfileEntity $profile */
                $profile = $this->profilesEntities[$score->id];
                $this->getStatPlane($profile, $score);
            }
        }
    }

    /**
     * @param ProfileEntity $profile
     * @param               $score
     * @return void
     * @throws NonUniqueResultException
     */
    private function getStatPlane(Profile &$profile, $score)
    {
        if (!$profile->getIsAlive() || !$profile->getIsActive()) {
            return;
        }
        $now       = new \DateTime("NOW");
        $statsDate = (new \DateTime())->setTimestamp(strtotime('previous monday', $now->getTimestamp()));
        if ($this->semaine == 0) {
            $statsDate = (new \DateTime())->setTimestamp(strtotime('next monday', $now->getTimestamp()));
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
                  ->setDateId(sprintf('%s-%s', $statsDate->format('Y'), $statsDate->format('W')))
        ;
        $profile->addPlane($planeStat);
        return;
    }
}
