<?php

namespace App\Entity\KillsStats;

use App\Entity\Profile\Profile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KillsStats\PlaneRepository")
 */
class Plane
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $kills;

    /**
     * @ORM\Column(type="integer")
     */
    private $money;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profile\Profile", inversedBy="planes")
     */
    private $profile;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $dateId;

    /**
     * Plane constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $now          = new \DateTime("NOW");
        $this->date   = $now;
        $this->kills  = 0;
        $this->money  = 0;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return $this
     */
    public function setKills(int $kills): self
    {
        $this->kills = $kills;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMoney(): ?int
    {
        return $this->money;
    }

    /**
     * @param int $money
     * @return $this
     */
    public function setMoney(): self
    {
        if ($this->kills < 75) {
            $this->money = 0;
            return $this;
        }
        if ($this->kills > 750) {
            $this->money = 7500;
            return $this;
        }
        $this->money = $this->kills * 10;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param \DateTimeInterface $date
     * @return $this
     */
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Profile|null
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * @param Profile|null $profile
     * @return $this
     */
    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateId()
    {
        return $this->dateId;
    }

    /**
     * @param mixed $dateId
     * @return Plane
     */
    public function setDateId(?string $dateId)
    {
        $this->dateId = $dateId;
        return $this;
    }
}
