<?php

namespace Webstack\UserBundle\Manager;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Webstack\UserBundle\Model\User;
use Webstack\UserBundle\Util\PasswordUpdaterInterface;

class UserManager
{
    private PasswordUpdaterInterface $passwordUpdater;
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private TokenGeneratorInterface $tokenGenerator;
    private RouterInterface $router;
    private MailerInterface $mailer;
    private bool $passwordCompromised;
    private int $minLength;
    private int $minStrength;

    public function __construct(PasswordUpdaterInterface $passwordUpdater, EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag, TokenGeneratorInterface $tokenGenerator, RouterInterface $router, MailerInterface $mailer, bool $passwordCompromised, int $minLength, int $minStrength)
    {
        $this->passwordUpdater = $passwordUpdater;
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->passwordCompromised = $passwordCompromised;
        $this->minLength = $minLength;
        $this->minStrength = $minStrength;
    }

    public function createUser(): User
    {
        $class = $this->getUserClass();

        return new $class();
    }

    public function create(User $user, bool $persist = true, bool $andFlush = true): User
    {
        if (true === $persist) {
            $this->entityManager->persist($user);
        }

        if (true === $andFlush) {
            $this->entityManager->flush();
        }

        return $user;
    }

    public function findUser(string $emailOrUsername): ?User
    {
        $userEntity = $this->getUserClass();

        if (false !== strpos($emailOrUsername, '@')) {
            $criteria = [
                'email' => $emailOrUsername,
            ];
        } else {
            $criteria = [
                'username' => $emailOrUsername,
            ];
        }

        return $this->entityManager->getRepository($userEntity)->findOneBy($criteria);
    }

    public function getUserClass(): string
    {
        return $this->parameterBag->get('webstack_user.model.user.class');
    }

    /**
     * @return object|User|null
     */
    public function findUserByConfirmationToken(string $token): ?object
    {
        return $this->findUserBy([
            'confirmationToken' => $token,
        ]);
    }

    public function updatePassword(UserInterface $user): void
    {
        $this->passwordUpdater->hashPassword($user);
    }

    protected function getPasswordUpdater(): PasswordUpdaterInterface
    {
        return $this->passwordUpdater;
    }

    /**
     * @param array<string, string> $criteria
     *
     * @return object|User|null
     */
    public function findUserBy(array $criteria): ?object
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    protected function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository($this->getUserClass());
    }

    /**
     * @return array<User>
     */
    public function findUsers(): array
    {
        return $this->getRepository()->findAll();
    }

    public function reloadUser(UserInterface $user): void
    {
        $this->entityManager->refresh($user);
    }

    /**
     * @param UserInterface&User $user
     */
    public function updateUser(UserInterface $user, bool $andFlush = true): void
    {
        $this->updatePassword($user);

        $emailAsUsername = $this->parameterBag->get('webstack_user.model.user.class.email_as_username');

        if ($emailAsUsername) {
            $user->setUsername($user->getEmail());
        }

        $this->entityManager->persist($user);

        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function deleteUser(UserInterface $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendInvitation(User $user): void
    {
        $fromEmail = $this->parameterBag->get('webstack_user.registration.from_email');

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $user->setPasswordRequestedAt(new DateTime());

        $this->entityManager->flush();

        $email = (new TemplatedEmail())
            ->from(new Address($fromEmail['address'], $fromEmail['sender_name']))
            ->to(new Address($user->getEmail(), $user->getLastName()))
            ->subject('Bevestig uw account')
            ->htmlTemplate('@WebstackUser/email/invitation/index.html.twig')
            ->context([
                'user' => $user,
                'confirmationUrl' => $this->router->generate('webstack_user_reset_password_reset', [
                    'token' => $user->getConfirmationToken(),
                ], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->mailer->send($email);
    }

    /**
     * @return array<PasswordStrength>
     */
    public function getPasswordConstraints(): array
    {
        $notPasswordCompromised = null;

        $passwordStrength = new PasswordStrength([
            'minStrength' => $this->minStrength,
            'minLength' => $this->minLength,
        ]);

        if ($this->passwordCompromised) {
            $notPasswordCompromised = [
                new NotCompromisedPassword([
                    'message' => 'Het ingevulde wachtwoord kan niet worden gebruikt omdat deze voorkomt op een lijst met gelekte wachtwoorden.',
                ]),
                $passwordStrength,
            ];
        }

        return $notPasswordCompromised ?? [$passwordStrength];
    }
}
