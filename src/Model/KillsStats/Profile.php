<?php

namespace App\Model\KillsStats;

class Profile
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $kills;

    /**
     * @var integer
     */
    private $money;

    /**
     * @var integer
     */
    private $umId;

    /**
     * @var string
     */
    private $umName;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Profile
     */
    public function setName(string $name): Profile
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Profile
     */
    public function setId(int $id): Profile
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getKills(): int
    {
        return $this->kills;
    }

    /**
     * @param int $kills
     * @return Profile
     */
    public function setKills(int $kills): Profile
    {
        if ($kills < 75) {
            $this->kills = 0;
            return $this;
        }
        if ($kills > 750) {
            $this->kills = 750;
            return $this;
        }
        $this->kills = $kills;
        return $this;
    }

    /**
     * @return int
     */
    public function getMoney(): int
    {
        return $this->money;
    }

    /**
     * @return Profile
     */
    public function setMoney(): Profile
    {
        $this->money = $this->kills * 10;
        return $this;
    }

    /**
     * @return int
     */
    public function getUmId(): int
    {
        return $this->umId;
    }

    /**
     * @param int $umId
     * @return Profile
     */
    public function setUmId(int $umId): Profile
    {
        $this->umId = $umId;
        return $this;
    }

    /**
     * @return string
     */
    public function getUmName(): string
    {
        return $this->umName;
    }

    /**
     * @param string $umName
     * @return Profile
     */
    public function setUmName(string $umName): Profile
    {
        $this->umName = $umName;
        return $this;
    }
}