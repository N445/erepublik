<?php

namespace App\Entity\Profile;

use App\Entity\KillsStats\Plane;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Profile\ProfileRepository")
 */
class Profile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="integer",unique=true)
     */
    private $identifier;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profile\UniteMilitaire", inversedBy="profiles", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $unitemilitaire;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\KillsStats\Plane", mappedBy="profile", cascade={"persist","remove"})
     */
    private $planes;

    /**
     * Profile constructor.
     * @param $name
     * @param $identifier
     */
    public function __construct($identifier, $name = null)
    {
        $this->planes     = new ArrayCollection();
        $this->name       = $name;
        $this->identifier = $identifier;
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
    public function setName(?string $name): self
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
     * @return UniteMilitaire|null
     */
    public function getUnitemilitaire(): ?UniteMilitaire
    {
        return $this->unitemilitaire;
    }

    /**
     * @param UniteMilitaire|null $unitemilitaire
     * @return $this
     */
    public function setUnitemilitaire(?UniteMilitaire $unitemilitaire): self
    {
        $this->unitemilitaire = $unitemilitaire;

        return $this;
    }

    /**
     * @return Collection|Plane[]
     */
    public function getPlanes(): Collection
    {
        return $this->planes;
    }

    /**
     * @param Plane $plane
     * @return $this
     */
    public function addPlane(Plane $plane): self
    {
        if (!$this->planes->contains($plane)) {
            $this->planes[] = $plane;
            $plane->setProfile($this);
        }

        return $this;
    }

    /**
     * @param Plane $plane
     * @return $this
     */
    public function removePlane(Plane $plane): self
    {
        if ($this->planes->contains($plane)) {
            $this->planes->removeElement($plane);
            // set the owning side to null (unless already changed)
            if ($plane->getProfile() === $this) {
                $plane->setProfile(null);
            }
        }

        return $this;
    }
}
