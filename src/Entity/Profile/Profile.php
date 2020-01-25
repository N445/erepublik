<?php

namespace App\Entity\Profile;

use App\Entity\KillsStats\Plane;
use App\Utils\ProfileHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Profile\ProfileRepository")
 * @UniqueEntity("identifier")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Profile\UniteMilitaire", inversedBy="profiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $unitemilitaire;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\KillsStats\Plane", mappedBy="profile", cascade={"persist","remove"})
     */
    private $planes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAlive;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $level;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $planeLevel;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbPaiementMissed;

    /**
     * Profile constructor.
     * @param $identifier
     * @param $name
     * @throws \Exception
     */
    public function __construct($identifier = null, $name = null)
    {
        $this->planes           = new ArrayCollection();
        $this->name             = $name;
        $this->identifier       = $identifier;
        $this->isAlive          = true;
        $this->isActive         = true;
        $this->createdAt        = new \DateTime("NOW");
        $this->status           = ProfileHelper::ACTIVE;
        $this->nbPaiementMissed = 0;
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

    /**
     * @return bool|null
     */
    public function getIsAlive(): ?bool
    {
        return $this->isAlive;
    }

    /**
     * @param bool $isAlive
     * @return $this
     */
    public function setIsAlive(bool $isAlive): self
    {
        $this->isAlive = $isAlive;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     * @return Profile
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getPlaneLevel(): ?int
    {
        return $this->planeLevel;
    }

    public function setPlaneLevel(?int $planeLevel): self
    {
        $this->planeLevel = $planeLevel;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getNbPaiementMissed(): ?int
    {
        return $this->nbPaiementMissed;
    }

    public function setNbPaiementMissed(?int $nbPaiementMissed): self
    {
        $this->nbPaiementMissed = $nbPaiementMissed;

        return $this;
    }


}
