<?php

namespace Webstack\UserBundle\Util;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Webstack\UserBundle\Model\User;

readonly class PasswordUpdater implements PasswordUpdaterInterface
{
    public function __construct(
        private PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    /**
     * @param UserInterface&User $user
     */
    public function hashPassword(UserInterface $user): void
    {
        $plainPassword = $user->getPlainPassword();

        if (!$plainPassword) {
            return;
        }

        $encoder = $this->passwordHasherFactory->getPasswordHasher($user);

        $hashedPassword = $encoder->hash($plainPassword);

        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
    }
}
