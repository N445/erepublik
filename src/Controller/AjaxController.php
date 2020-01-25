<?php

namespace App\Controller;

use App\Repository\Profile\ProfileRepository;
use App\Service\Erepublik\ProfilePopulator;
use App\Utils\ProfileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AjaxController
 * @package App\Controller
 * @Route("/ajax")
 */
class AjaxController extends AbstractController
{
    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var ProfilePopulator
     */
    private $profilePopulator;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AjaxController constructor.
     * @param ProfileRepository      $profileRepository
     * @param ProfilePopulator       $profilePopulator
     * @param EntityManagerInterface $em
     */
    public function __construct(
        ProfileRepository $profileRepository,
        ProfilePopulator $profilePopulator,
        EntityManagerInterface $em
    )
    {
        $this->profileRepository = $profileRepository;
        $this->profilePopulator  = $profilePopulator;
        $this->em                = $em;
    }

    /**
     * @Route("/add-nb-paiement/{identifier}", name="APP_NB_PAIEMENT", options={"expose"=true})
     */
    public function index($identifier)
    {
        if (!$profile = $this->profileRepository->getProfileByIdentifier($identifier)) {
            return $this->json([
                'success' => false,
            ]);
        }

        $profile->setNbPaiementMissed($profile->getNbPaiementMissed() + 1);
        if ($profile->getNbPaiementMissed() >= 4) {
            $profile->setStatus(ProfileHelper::INACTIVE);
        }
        $this->profilePopulator->setProfileInformations($profile);
        $this->em->flush();
        return $this->json([
            'success' => true,
            'html'    => $this->renderView('includes/profile/row.html.twig', [
                'profile' => $profile,
            ]),
        ]);
    }

    /**
     * @Route("/reset-nb-paiement/{identifier}", name="RESET_NB_PAIEMENT", options={"expose"=true})
     */
    public function reset($identifier)
    {
        if (!$profile = $this->profileRepository->getProfileByIdentifier($identifier)) {
            return $this->json([
                'success' => false,
            ]);
        }

        $profile
            ->setNbPaiementMissed(0)
            ->setStatus(ProfileHelper::ACTIVE)
        ;
        $this->profilePopulator->setProfileInformations($profile);
        $this->em->flush();
        return $this->json([
            'success' => true,
            'html'    => $this->renderView('includes/profile/row.html.twig', [
                'profile' => $profile,
            ]),
        ]);
    }
}
