<?php

namespace App\Service\Erepublik;

use App\Clients\Erepublik;
use App\Entity\Profile\Profile;
use App\Entity\Profile\UniteMilitaire;
use App\Repository\Profile\UniteMilitaireRepository;
use App\Utils\ProfileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Exception\ClientException;

class ProfilePopulator
{
    /**
     * @var Erepublik
     */
    private $erepublikClient;

    /**
     * @var UniteMilitaire[]
     */
    private $umEntities;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UniteMilitaireRepository
     */
    private $militaireRepository;


    /**
     * Profile constructor.
     * @param EntityManagerInterface   $em
     * @param Erepublik                $erepublikClient
     * @param UniteMilitaireRepository $militaireRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        Erepublik $erepublikClient,
        UniteMilitaireRepository $militaireRepository)
    {
        $this->em                  = $em;
        $this->erepublikClient     = $erepublikClient;
        $this->militaireRepository = $militaireRepository;
    }

    public function setProfileInformations(Profile &$profile)
    {
        if (!$response = $this->profileRequest($profile)) {
            return false;
        }

        $profileData = json_decode($response->getBody()->getContents());

        $profile->setName($profileData->citizen->name)
                ->setIsAlive($profileData->citizen->is_alive)
                ->setIsActive($profileData->military->militaryData->aircraft->rankNumber < ProfileHelper::MAX_PLANE_LEVEL)
                ->setLevel($profileData->citizen->level)
                ->setPlaneLevel($profileData->military->militaryData->aircraft->rankNumber)
                ->setUnitemilitaire($this->getUniteMilitaire(
                    $profileData->military->militaryUnit
                ))
        ;

        if (!$profile->getCreatedAt()) {
            $profile->setCreatedAt(new \DateTime("NOW"));
        }

        if (!$profile->getNbPaiementMissed()) {
            $profile->setNbPaiementMissed(0);
        }

        if ($profile->getStatus() !== ProfileHelper::DESACTIVE) {
            $this->setProfileStatus($profile, $profileData);
        }

        return $profile;
    }

    /**
     * @param Profile $profile
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    private function profileRequest(Profile $profile)
    {
        try {
            return $this->erepublikClient
                ->get(
                    sprintf('/fr/main/citizen-profile-json/%s'
                        , $profile->getIdentifier()
                    )
                );
        } catch (ClientException $exception) {
            return false;
        }
    }

    private function setProfileStatus(Profile &$profile, $profileData)
    {
        if (!$profileData->citizen->is_alive) {
            $profile->setStatus(ProfileHelper::DEAD);
            return;
        }
        if ($profileData->military->militaryData->aircraft->rankNumber >= ProfileHelper::MAX_PLANE_LEVEL) {
            $profile->setStatus(ProfileHelper::LEVELMAX);
            return;
        }
        if ($profile->getNbPaiementMissed() >= 4) {
            $profile->setStatus(ProfileHelper::INACTIVE);
            return;
        }
        $profile->setStatus(ProfileHelper::ACTIVE);
    }

    /**
     * @param $dataUniteMilitaire
     * @return UniteMilitaire|mixed|null
     * @throws NonUniqueResultException
     */
    private function getUniteMilitaire($dataUniteMilitaire)
    {
        $identifier = $dataUniteMilitaire->id;

        if ($um = $this->militaireRepository->getUnitemilitaireByIdentifier($identifier)) {
            return $um;
        }

        $um = new UniteMilitaire();

        $um->setIdentifier($identifier)
           ->setName(trim($dataUniteMilitaire->name))
        ;

        $this->umEntities[$um->getIdentifier()] = $um;
        $this->em->persist($um);
        return $um;
    }
}
