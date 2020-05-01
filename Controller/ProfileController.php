<?php

namespace Webstack\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Webstack\UserBundle\Form\Type\ProfileFormType;

/**
 * Class ProfileController
 */
class ProfileController extends AbstractController
{
    /**
     * @Template()
     *
     * @return array
     */
    public function index(): array
    {
        $user = $this->getUser();

        return [
            'user' => $user
        ];
    }

    /**
     * @Template()
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function edit(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfileFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Uw gegevens is succesvol aangepast.');

            return $this->redirectToRoute('webstack_user_profile_index');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
