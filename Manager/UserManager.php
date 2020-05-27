<?php

namespace Webstack\UserBundle\Manager;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Webstack\UserBundle\Model\User;
use Webstack\UserBundle\Util\PasswordUpdaterInterface;

/**
 * Class UserManager
 */
class UserManager
{
    /**
     * @var PasswordUpdaterInterface
     */
    private $passwordUpdater;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * UserManager constructor.
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param Registry $registry
     * @param ParameterBagInterface $parameterBag
     * @param TokenGeneratorInterface $tokenGenerator
     * @param RouterInterface $router
     * @param MailerInterface $mailer
     */
    public function __construct(PasswordUpdaterInterface $passwordUpdater, Registry $registry, ParameterBagInterface $parameterBag, TokenGeneratorInterface $tokenGenerator, RouterInterface $router, MailerInterface $mailer)
    {
        $this->passwordUpdater = $passwordUpdater;
        $this->entityManager = $registry->getManager();
        $this->parameterBag = $parameterBag;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->mailer = $mailer;
    }

    /**
     * @return mixed
     */
    public function createUser()
    {
        $class = $this->getUserClass();

        return new $class();
    }


    /**
     * @param User $user
     * @param bool $persist
     * @param bool $andFlush
     * @return User
     */
    public function create(User $user, $persist = true, $andFlush = true): User
    {
        if (true === $persist) {
            $this->entityManager->persist($user);
        }

        if ($andFlush) {
            $this->entityManager->flush();
        }

        return $user;
    }

    /**
     * @param string $emailOrUsername
     * @return User|null
     */
    public function findUser(string $emailOrUsername): ?User
    {
        $userEntity = $this->getUserClass();

        if (strpos($emailOrUsername, '@') !== false) {
            $criteria = [
                'email' => $emailOrUsername
            ];
        } else {
            $criteria = [
                'username' => $emailOrUsername
            ];
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository($userEntity)->findOneBy($criteria);

        return $user;
    }

    /**
     * @return string
     */
    public function getUserClass(): string
    {
        return $this->parameterBag->get('webstack_user.model.user.class');
    }

    /**
     * @param $token
     * @return object|UserInterface|null
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->findUserBy(array('confirmationToken' => $token));
    }

    /**
     * @param UserInterface $user
     */
    public function updatePassword(UserInterface $user)
    {
        $this->passwordUpdater->hashPassword($user);
    }

    /**
     * @return PasswordUpdaterInterface
     */
    protected function getPasswordUpdater()
    {
        return $this->passwordUpdater;
    }

    /**
     * @param array $criteria
     * @return object|null
     */
    public function findUserBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->getUserClass());
    }

    /**
     * @return array
     */
    public function findUsers()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param UserInterface $user
     */
    public function reloadUser(UserInterface $user)
    {
        $this->entityManager->refresh($user);
    }

    /**
     * @param UserInterface $user
     * @param bool $andFlush
     */
    public function updateUser(UserInterface $user, $andFlush = true)
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

    /**
     * @param UserInterface $user
     */
    public function deleteUser(UserInterface $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @throws TransportExceptionInterface
     */
    public function sendInvitation(User $user): void
    {
        if (null !== $user) {
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
                        'token' => $user->getConfirmationToken()
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);

            $this->mailer->send($email);
        }
    }


}
