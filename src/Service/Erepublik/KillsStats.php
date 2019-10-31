<?php

namespace App\Service\Erepublik;

use App\Clients\Erepublik;
use App\Entity\Profile\Profile as ProfileEntity;
use App\Entity\Profile\UniteMilitaire;
use App\Model\KillsStats\Profile;
use App\Repository\Profile\ProfileRepository;
use App\Repository\Profile\UniteMilitaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

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
    private $umIds = [];

    /**
     * @var array
     */
    private $leaderboards = [];

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

    public function run()
    {
        if (!$this->cookie) {
            return new \Exception("Cookie not set");
        }
        if (empty($this->profiles) || empty($this->umIds)) {
            return [];
        }
        $this->browseLeaderBoards();
        return $this->profiles;
    }

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
     */
    private function setProfileKills($scores)
    {
        foreach ($scores as $score) {
            if (array_key_exists($score->id, $this->profiles)) {
                $this->profiles[$score->id]->setKills($score->values)->setMoney();
            }
        }
    }

    /**
     * @param ProfileEntity $profile
     * @return ProfileEntity
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     */
    private function getProfile(ProfileEntity $profile)
    {
        if ($profileEntity = $this->profileRepository->getProfileByIdentifier($profile->getId())) {
            return $profileEntity;
        }

        return $this->cache->get($profile->getId(), function (ItemInterface $item) use ($profile) {
            $item->expiresAfter(3600);
            $response    = $this->erepublikClient->get(sprintf('/fr/main/citizen-profile-json/%s', $profile->getId()));
            $json        = $response->getBody()->getContents();
            $profileData = json_decode($json);
            $profile->setName($profile->getName() ? $profile->getName() : $profileData->citizen->name);
            if ($profileData->isBanned) {
                $profile->setValid(false);
                return $profile;
            }

            $profile->setUnitemilitaire(
                $this->getUniteMilitaire(
                    $profileData->military->militaryUnit->id,
                    $profileData->military->militaryUnit->name
                )
            );

            $this->em->persist($profile);

            return $profile;
        });
    }

    /**
     * @param $identifier
     * @param $name
     * @return UniteMilitaire|mixed|null
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     */
    private function getUniteMilitaire($identifier, $name)
    {
        if ($umEntity = $this->militaireRepository->getUnitemilitaireByIdentifier($identifier)) {
            return $umEntity;
        }

        return $this->cache->get($identifier, function (ItemInterface $item) use ($identifier, $name) {
            $item->expiresAfter(3600);

            $um = new UniteMilitaire();

            $um->setIdentifier($identifier)
               ->setName($name)
            ;

            $this->em->persist($um);

            return $um;
        });
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
     * @throws InvalidArgumentException
     */
    public function setProfilesAndUmIds(array $profiles): KillsStats
    {
        /** @var ProfileEntity $profile */
        foreach ($profiles as $profile) {
            $profile = $this->getProfile($profile);
            dump($profile);
            die;

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
}