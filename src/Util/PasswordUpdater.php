<?php

namespace Webstack\UserBundle\Util;

use Exception;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Webstack\UserBundle\Model\User;

class PasswordUpdater implements PasswordUpdaterInterface
{
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    public function __construct(PasswordHasherFactoryInterface $passwordHasherFactory)
    {
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    /**
     * @param UserInterface&User $user
     *
     * @throws Exception
     */
    public function hashPassword(UserInterface $user): void
    {
        $plainPassword = $user->getPlainPassword();

        if ('' === $plainPassword) {
            return;
        }

        $encoder = $this->passwordHasherFactory->getPasswordHasher($user);

        $hashedPassword = $encoder->hash($plainPassword);
        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
    }
}
