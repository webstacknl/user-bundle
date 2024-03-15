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
    public function __construct(
        private readonly PasswordHasherFactoryInterface $encoderFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
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

            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();

            $password = $passwordHasher->hash($plainPassword);

            $user->setPassword($password);

            $this->entityManager->flush();

            $this->addFlash('success', 'Uw wachtwoord is gewijzigd.');

            return $this->redirectToRoute('app_home_index');
        }

        return $this->render('@WebstackUser/change_password/index.html.twig', [
            'form' => $form,
        ]);
    }

    public static function getTitle(): string
    {
        return 'Wachtwoord wijzigen';
    }
}
