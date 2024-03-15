<?php

namespace Webstack\UserBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Webstack\UserBundle\Model\User;
use Webstack\UserBundle\Util\PasswordUpdaterInterface;

class UserManager
{
    public function __construct(
        private readonly PasswordUpdaterInterface $passwordUpdater,
        private readonly ManagerRegistry $managerRegistry,
        private readonly ParameterBagInterface $parameterBag,
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly RouterInterface $router,
        private readonly MailerInterface $mailer,
        private readonly bool $passwordCompromised,
        private readonly int $minLength,
        private readonly int $minStrength,
    ) {
    }

    public function createUser(): User
    {
        /** @var User $class */
        $class = $this->getUserClass();

        return new $class();
    }

    public function create(User $user, bool $persist = true, bool $andFlush = true): User
    {
        if (true === $persist) {
            $this->getEntityManager()->persist($user);
        }

        if (true === $andFlush) {
            $this->getEntityManager()->flush();
        }

        return $user;
    }

    public function findUser(string $emailOrUsername): ?User
    {
        $userEntity = $this->getUserClass();

        if (str_contains($emailOrUsername, '@')) {
            $criteria = [
                'email' => $emailOrUsername,
            ];
        } else {
            $criteria = [
                'username' => $emailOrUsername,
            ];
        }

        /** @var User|null $user */
        $user = $this->getEntityManager()->getRepository($userEntity)->findOneBy($criteria);

        return $user;
    }

    /**
     * @return class-string
     */
    public function getUserClass(): string
    {
        /** @var class-string $class */
        $class = $this->parameterBag->get('webstack_user.model.user.class');

        return $class;
    }

    public function findUserByConfirmationToken(string $token): ?User
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
     * @param array<string, mixed> $criteria
     */
    public function findUserBy(array $criteria): ?User
    {
        $user = $this->getRepository()->findOneBy($criteria);

        if (null === $user || $user instanceof User) {
            return $user;
        }

        throw new \DomainException();
    }

    protected function getRepository(): ObjectRepository
    {
        return $this->getEntityManager()->getRepository($this->getUserClass());
    }

    /**
     * @return list<User>
     */
    public function findUsers(): array
    {
        /** @var list<User> $users */
        $users = $this->getRepository()->findAll();

        return $users;
    }

    public function reloadUser(UserInterface $user): void
    {
        $this->getEntityManager()->refresh($user);
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

        $this->getEntityManager()->persist($user);

        if ($andFlush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteUser(UserInterface $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendInvitation(User $user): void
    {
        /** @var array{address: string, sender_name: string} $fromEmail */
        $fromEmail = $this->parameterBag->get('webstack_user.registration.from_email');

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $user->setPasswordRequestedAt(new \DateTime());

        $this->getEntityManager()->flush();

        $email = (new TemplatedEmail())
            ->from(new Address($fromEmail['address'], $fromEmail['sender_name']))
            ->to(new Address($user->getEmail(), (string) $user->getLastName()))
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
     * @return array<Constraint>
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

    private function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass($this->getUserClass());

        return $entityManager;
    }
}
