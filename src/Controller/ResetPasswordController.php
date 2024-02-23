<?php

namespace Webstack\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Webstack\UserBundle\Form\Type\ResetPasswordType;
use Webstack\UserBundle\Manager\UserManager;

class ResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TokenGeneratorInterface $tokenGenerator;
    private UserManager $userManager;
    private MailerInterface $mailer;
    private RouterInterface $router;
    private PasswordHasherFactoryInterface $passwordHasherFactory;
    private array $fromEmail;
    private string $userClass;

    public function __construct(EntityManagerInterface $entityManager, UserManager $userManager, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer, RouterInterface $router, PasswordHasherFactoryInterface $passwordHasherFactory, array $fromEmail, string $userClass)
    {
        $this->entityManager = $entityManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->passwordHasherFactory = $passwordHasherFactory;
        $this->fromEmail = $fromEmail;
        $this->userClass = $userClass;
    }

    public function request(): Response
    {
        return $this->render('reset_password/request.html.twig');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmail(Request $request): Response
    {
        $username = $request->request->get('username');

        $user = $this->userManager->findUser($username);

        if (null !== $user) {
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $user->setPasswordRequestedAt(new \DateTime());

            $this->entityManager->flush();

            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail['address'], $this->fromEmail['sender_name']))
                ->to(new Address($user->getEmail(), $user->getLastName()))
                ->subject('Wachtwoord resetten')
                ->htmlTemplate('@WebstackUser/email/reset-password/confirm.html.twig')
                ->context([
                    'user' => $user,
                    'confirmationUrl' => $this->router->generate('webstack_user_reset_password_reset', [
                        'token' => $user->getConfirmationToken(),
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                ]);

            $this->mailer->send($email);
        }

        return $this->redirectToRoute('webstack_user_reset_password_check_email', [
            'username' => $username,
        ]);
    }

    public function checkEmail(Request $request): Response
    {
        $username = $request->query->get('username');

        if (null === $username) {
            return new RedirectResponse($this->generateUrl('webstack_user_reset_password_index'));
        }

        return $this->render('@WebstackUser/reset_password/check_email.html.twig', [
            'username' => $username,
        ]);
    }

    public function reset(Request $request, string $token): Response
    {
        $user = $this->entityManager->getRepository($this->userClass)->findOneBy([
            'confirmationToken' => $token,
        ]);

        if (null === $user) {
            return $this->redirectToRoute('app_security_login');
        }

        $form = $this->createForm(ResetPasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($user);
            $password = $passwordHasher->hash($form->get('password')->getData());

            $user->setPassword($password);
            $user->setConfirmationToken(null);

            $this->entityManager->flush();

            return $this->redirectToRoute('app_security_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
            'token' => $token,
        ]);
    }
}
