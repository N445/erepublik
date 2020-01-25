<?php

namespace App\Controller\Profile;

use App\Entity\Profile\Profile;
use App\Form\Profile\ProfileType;
use App\Repository\Profile\ProfileRepository;
use App\Service\Erepublik\ProfilePopulator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/profile")
 */
class ProfileController extends AbstractController
{

    /**
     * @var ProfilePopulator
     */
    private $profilePopulator;

    public function __construct(ProfilePopulator $profilePopulator)
    {
        $this->profilePopulator = $profilePopulator;
    }

    /**
     * @Route("/", name="profile_profile_index", methods={"GET"})
     */
    public function index(ProfileRepository $profileRepository): Response
    {
        return $this->render('profile/profile/index.html.twig', [
            'profiles' => $profileRepository->getProfilesAdmin(),
        ]);
    }

    /**
     * @Route("/new", name="profile_profile_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $profile = new Profile();
        $form    = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->profilePopulator->setProfileInformations($profile)) {
                $this->addFlash('danger', sprintf('L\'identifiant %s n\'est pas valide', $profile->getIdentifier()));
                return $this->redirectToRoute('profile_profile_new');
            }
            $this->addFlash('success', 'Le profile %s (%s) a bien été créé', $profile->getName(), $profile->getIdentifier());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($profile);
            $entityManager->flush();

            return $this->redirectToRoute('profile_profile_index');
        }

        return $this->render('profile/profile/new.html.twig', [
            'profile' => $profile,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="profile_profile_show", methods={"GET"})
     */
    public function show(Profile $profile): Response
    {
        return $this->render('profile/profile/show.html.twig', [
            'profile' => $profile,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="profile_profile_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Profile $profile): Response
    {
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('profile_profile_index');
        }

        return $this->render('profile/profile/edit.html.twig', [
            'profile' => $profile,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="profile_profile_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Profile $profile): Response
    {
        if ($this->isCsrfTokenValid('delete' . $profile->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($profile);
            $entityManager->flush();
        }

        return $this->redirectToRoute('profile_profile_index');
    }
}
