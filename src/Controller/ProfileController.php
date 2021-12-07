<?php

namespace Webstack\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Webstack\UserBundle\Form\Type\ProfileFormType;

class ProfileController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Template()
     */
    public function index(): array
    {
        return [
            'user' => $this->getUser(),
        ];
    }

    /**
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Uw gegevens is succesvol aangepast.');

            return $this->redirectToRoute('webstack_user_profile_index');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
