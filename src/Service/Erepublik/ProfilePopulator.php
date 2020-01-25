<?php

namespace App\Service\Erepublik;

use App\Clients\Erepublik;
use App\Entity\Profile\Profile;
use App\Entity\Profile\UniteMilitaire;
use App\Repository\Profile\UniteMilitaireRepository;
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
     * Profile constructor.
     * @param Erepublik                $erepublikClient
     * @param UniteMilitaireRepository $militaireRepository
     */
    public function __construct(
        Erepublik $erepublikClient,
        UniteMilitaireRepository $militaireRepository)
    {
        $this->erepublikClient = $erepublikClient;
        $this->umEntities      = $militaireRepository->findAll();
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
     */
    private function getUniteMilitaire($dataUniteMilitaire)
    {
        $identifier = $dataUniteMilitaire->id;

        if (array_key_exists($identifier, $this->umEntities)) {
            return $this->umEntities[$identifier];
        }

        $um = new UniteMilitaire();

        $um->setIdentifier($identifier)
           ->setName(trim($dataUniteMilitaire->name))
        ;

        $this->umEntities[$um->getIdentifier()] = $um;

        return $um;
    }
}
