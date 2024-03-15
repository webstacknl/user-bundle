<?php

namespace Webstack\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webstack\UserBundle\Form\Type\ProfileFormType;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function index(): Response
    {
        return $this->render('@WebstackUser/profile/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Uw gegevens is succesvol aangepast.');

            return $this->redirectToRoute('webstack_user_profile_index');
        }

        return $this->render('@WebstackUser/profile/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
