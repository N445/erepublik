<?php

namespace App\Service\Erepublik;

use App\Clients\Erepublik;
use App\Model\KillsStats\Profile;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

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

    public function __construct(Erepublik $erepublikClient)
    {
        $this->erepublikClient = $erepublikClient;
    }

    public function run()
    {
        $this->browseLeaderBoards();
        return $this->profiles;
    }

    /**
     * @param array $profilesIds
     * @return KillsStats
     */
    public function setProfilesAndUmIds(array $profilesIds): KillsStats
    {
        foreach ($profilesIds as $profilesId) {
            $profileData = json_decode($this->erepublikClient
                ->get(sprintf('/fr/main/citizen-profile-json/%s', $profilesId))
                ->getBody()
                ->getContents());

            $profile = new Profile();
            $profile->setId($profilesId)
                    ->setName($profileData->citizen->name)
            ;
            $this->profiles[$profilesId]                           = $profile;
            $this->umIds[$profileData->military->militaryUnit->id] = $profileData->military->militaryUnit->name;
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
            yield json_decode($this->erepublikClient
                ->get(sprintf('/fr/main/leaderboards-kills-aircraft-rankings/11/0/%s/0', $umId), [
                    'cookies' => $this->cookie,
                ])->getBody()->getContents())->top;
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
}