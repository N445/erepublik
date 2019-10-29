<?php

namespace App\Model\KillsStats;

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
     * @var integer
     */
    private $semaine;

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
        $this->profiles = str_replace("\r", '', explode(PHP_EOL, $profiles));
        return $this;
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
     * @return Search
     */
    public function setSemaine(int $semaine): Search
    {
        $this->semaine = $semaine;
        return $this;
    }
}