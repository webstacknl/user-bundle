<?php

namespace Webstack\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Webstack\UserBundle\Form\Type\ChangePasswordType;
use Webstack\UserBundle\Model\User;

/**
 * @method User|null getUser()
 */
class ChangePasswordController extends AbstractController
{
    private PasswordHasherFactoryInterface $encoderFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(PasswordHasherFactoryInterface $encoderFactory, EntityManagerInterface $entityManager)
    {
        $this->encoderFactory = $encoderFactory;
        $this->entityManager = $entityManager;
    }

    public function index(Request $request): Response
    {
        $user = $this->getUser();

        if (null === $user) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordHasher = $this->encoderFactory->getPasswordHasher($user);

            $password = $passwordHasher->hash($form->get('password')->getData());

            $user->setPassword($password);

            $this->entityManager->flush();

            $this->addFlash('success', 'Uw wachtwoord is gewijzigd.');

            return $this->redirectToRoute('app_home_index');
        }

        return $this->render('change_password/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public static function getTitle(): string
    {
        return 'Wachtwoord wijzigen';
    }
}
