<?php

namespace Webstack\UserBundle\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Webstack\UserBundle\Manager\UserManager;
use Webstack\UserBundle\Model\User;

class UserProvider implements UserProviderInterface
{
    protected UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->findUser($identifier);

        if (null === $user) {
            $exception = new UserNotFoundException();
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * @param UserInterface&User $user
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (false === $this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->userManager->getUserClass(), get_class($user)));
        }

        return $this->userManager->findUserBy([
            'id' => $user->getId(),
        ]);
    }

    public function supportsClass(string $class): bool
    {
        $userClass = $this->userManager->getUserClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    protected function findUser(string $username): ?UserInterface
    {
        return $this->userManager->findUser($username);
    }
}
