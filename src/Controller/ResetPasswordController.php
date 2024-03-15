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
use Webstack\UserBundle\Model\User;

class ResetPasswordController extends AbstractController
{
    /**
     * @param class-string $userClass
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserManager $userManager,
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly MailerInterface $mailer,
        private readonly RouterInterface $router,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly array $fromEmail,
        private readonly string $userClass,
    ) {
    }

    public function request(): Response
    {
        return $this->render('@WebstackUser/reset_password/request.html.twig');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmail(Request $request): Response
    {
        /** @var string $username */
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
                ->to(new Address($user->getEmail(), (string) $user->getLastName()))
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
        /** @var User|null $user */
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

            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();

            $password = $passwordHasher->hash($plainPassword);

            $user->setPassword($password);
            $user->setConfirmationToken(null);

            $this->entityManager->flush();

            return $this->redirectToRoute('app_security_login');
        }

        return $this->render('@WebstackUser/reset_password/reset.html.twig', [
            'form' => $form,
            'token' => $token,
        ]);
    }
}
