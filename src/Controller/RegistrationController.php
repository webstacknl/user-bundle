<?php

namespace Webstack\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Webstack\UserBundle\Form\Factory\FactoryInterface;
use Webstack\UserBundle\Manager\UserManager;

class RegistrationController extends AbstractController
{
    private FactoryInterface $formFactory;
    private UserManager $userManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        FactoryInterface $formFactory,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function register(Request $request): Response
    {
        $user = $this->userManager->createUser();
        $form = $this->formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updateUser($user);

            return $this->redirectToRoute('webstack_user_registration_confirmed');
        }

        return $this->render('@WebstackUser/Registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function confirmed(Request $request): Response
    {
        $user = $this->getUser();

        if (null === $user) {
            throw new AccessDeniedHttpException();
        }

        return $this->render('@WebstackUser/Registration/confirmed.html.twig', [
            'user' => $user,
            'targetUrl' => $this->getTargetUrlFromSession($request->getSession()),
        ]);
    }

    private function getTargetUrlFromSession(SessionInterface $session): ?string
    {
        $token = $this->tokenStorage->getToken();

        if (
            $token instanceof PreAuthenticatedToken ||
            $token instanceof RememberMeToken ||
            $token instanceof UsernamePasswordToken
        ) {
            $key = sprintf('_security.%s.target_path', $token->getFirewallName());

            if ($session->has($key)) {
                return $session->get($key);
            }
        }

        return null;
    }
}
