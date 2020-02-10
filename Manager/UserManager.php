<?php

namespace Webstack\UserBundle\Manager;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * UserManager constructor.
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param Registry $registry
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(PasswordUpdaterInterface $passwordUpdater, Registry $registry, ParameterBagInterface $parameterBag)
    {
        $this->passwordUpdater = $passwordUpdater;
        $this->entityManager = $registry->getManager();
        $this->parameterBag = $parameterBag;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->findUserBy(array('confirmationToken' => $token));
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function findUsers()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function reloadUser(UserInterface $user)
    {
        $this->entityManager->refresh($user);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function deleteUser(UserInterface $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
