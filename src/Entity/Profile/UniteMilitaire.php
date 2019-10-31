<?php

namespace App\Entity\Profile;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Profile\UniteMilitaireRepository")
 */
class UniteMilitaire
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="integer",unique=true)
     */
    private $identifier;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Profile\Profile", mappedBy="unitemilitaire")
     */
    private $profiles;

    /**
     * UniteMilitaire constructor.
     */
    public function __construct()
    {
        $this->profiles = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    /**
     * @param int $identifier
     * @return $this
     */
    public function setIdentifier(int $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return Collection|Profile[]
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    /**
     * @param Profile $profile
     * @return $this
     */
    public function addProfile(Profile $profile): self
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles[] = $profile;
            $profile->setUnitemilitaire($this);
        }

        return $this;
    }

    /**
     * @param Profile $profile
     * @return $this
     */
    public function removeProfile(Profile $profile): self
    {
        if ($this->profiles->contains($profile)) {
            $this->profiles->removeElement($profile);
            // set the owning side to null (unless already changed)
            if ($profile->getUnitemilitaire() === $this) {
                $profile->setUnitemilitaire(null);
            }
        }

        return $this;
    }
}
