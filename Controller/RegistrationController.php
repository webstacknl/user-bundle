<?php

namespace Webstack\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Webstack\UserBundle\Event\FormEvent;
use Webstack\UserBundle\Form\Factory\FactoryInterface;
use Webstack\UserBundle\Manager\UserManager;

/**
 * Class RegistrationController
 */
class RegistrationController extends AbstractController
{
    /**
     * @var FactoryInterface
     */
    private $formFactory;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * RegistrationController constructor.
     * @param FactoryInterface $formFactory
     * @param UserManager $userManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(FactoryInterface $formFactory, UserManager $userManager, TokenStorageInterface $tokenStorage)
    {
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function register(Request $request): Response
    {
        $user = $this->userManager->createUser();
        $form = $this->formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userManager->updateUser($user);

                return $this->redirectToRoute('webstack_user_registration_confirmed');
            }

            $event = new FormEvent($form, $request);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('@WebstackUser/Registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Tell the user his account is now confirmed.
     * @param Request $request
     * @return Response
     */
    public function confirmed(Request $request): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('@WebstackUser/Registration/confirmed.html.twig',[
            'user' => $user,
            'targetUrl' => $this->getTargetUrlFromSession($request->getSession()),
        ]);
    }

    /**
     * @param SessionInterface $session
     * @return string|null
     */
    private function getTargetUrlFromSession(SessionInterface $session): ?string
    {
        $key = sprintf('_security.%s.target_path', $this->tokenStorage->getToken()->getProviderKey());

        if ($session->has($key)) {
            return $session->get($key);
        }

        return null;
    }
}
