<?php

namespace Webstack\UserBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Webstack\UserBundle\Util\PasswordUpdaterInterface;

/**
 * Class UserManager
 */
abstract class UserManager implements UserManagerInterface
{
    /**
     * @var PasswordUpdaterInterface
     */
    private $passwordUpdater;

    /**
     * UserManager constructor.
     * @param PasswordUpdaterInterface $passwordUpdater
     */
    public function __construct(PasswordUpdaterInterface $passwordUpdater)
    {
        $this->passwordUpdater = $passwordUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser()
    {
        $class = $this->getClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsernameOrEmail($usernameOrEmail): ?UserInterface
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByConfirmationToken($token): ?UserInterface
    {
        return $this->findUserBy(array('confirmationToken' => $token));
    }

    /**
     * {@inheritdoc}
     */
    public function updatePassword(UserInterface $user): void
    {
        $this->passwordUpdater->hashPassword($user);
    }

    /**
     * @return PasswordUpdaterInterface
     */
    protected function getPasswordUpdater(): PasswordUpdaterInterface
    {
        return $this->passwordUpdater;
    }
}
