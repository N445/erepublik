<?php

namespace App\Service\Erepublik;

use App\Clients\Erepublik;
use App\Model\KillsStats\Profile;
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

    public function __construct(Erepublik $erepublikClient)
    {
        $this->erepublikClient = $erepublikClient;
        $this->cache           = new FilesystemAdapter();
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

    /**
     * @param array $profilesIds
     * @return KillsStats
     * @throws InvalidArgumentException
     */
    public function setProfilesAndUmIds(array $profilesIds): KillsStats
    {
        foreach ($profilesIds as $profilesId) {
            $profile = $this->getProfileById($profilesId);;
            $this->profiles[$profilesId]      = $profile;
            $this->umIds[$profile->getUmId()] = $profile->getUmName();
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

    private function browseLeaderBoards()
    {
        foreach ($this->getLeaderboards() as $leaderboard) {
            $this->setProfileKills($leaderboard);
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
     * @param $id
     * @return Profile
     * @throws InvalidArgumentException
     */
    private function getProfileById($id)
    {
        return $this->cache->get($id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600);
            $response    = $this->erepublikClient->get(sprintf('/fr/main/citizen-profile-json/%s', $id));
            $json        = $response->getBody()->getContents();
            $profileData = json_decode($json);

            $profile = new Profile();
            $profile->setId($id)
                    ->setName($profileData->citizen->name)
                    ->setUmId($profileData->military->militaryUnit->id)
                    ->setUmName($profileData->military->militaryUnit->name)
            ;

            return $profile;
        });
    }

    /**
     * @return int
     */
    public function getSemaine(): int
    {
        return $this->semaine;
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
}