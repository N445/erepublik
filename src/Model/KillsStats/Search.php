<?php

namespace App\Model\KillsStats;

use App\Entity\Profile\Profile;
use App\Utils\MondayHelper;

class Search
{
    /**
     * @var string
     */
    private $cookie;

    /**
     * @var Profile[]
     */
    private $profiles;

    /**
     * @var string
     */
    private $semaine;

    public function __construct()
    {
        $this->semaine = MondayHelper::PREV_MONDAY;
    }

    /**
     * @return string
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param string $cookie
     * @return Search
     */
    public function setCookie(string $cookie): Search
    {
        $this->cookie = $cookie;
        return $this;
    }

    /**
     * @return Profile[]
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * @param string $profiles
     * @return Search
     */
    public function setProfiles($profiles): Search
    {
        $this->profiles = $profiles;
        return $this;
    }

    /**
     * @return string
     */
    public function getSemaine()
    {
        return $this->semaine;
    }

    /**
     * @param string $semaine
     * @return Search
     */
    public function setSemaine(string $semaine): Search
    {
        $this->semaine = $semaine;
        return $this;
    }
}
