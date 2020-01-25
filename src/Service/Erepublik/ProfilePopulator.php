<?php

namespace App\Service\Erepublik;

use App\Clients\Erepublik;
use App\Entity\Profile\Profile;
use App\Entity\Profile\UniteMilitaire;
use App\Repository\Profile\UniteMilitaireRepository;
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

        $rankLevel = $profileData->military->militaryData->aircraft->rankNumber;

        $profile->setName($profileData->citizen->name)
                ->setIsAlive($profileData->citizen->is_alive)
                ->setIsActive($rankLevel < 44)
                ->setUnitemilitaire($this->getUniteMilitaire(
                    $profileData->military->militaryUnit
                ))
        ;

        return true;
    }

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
