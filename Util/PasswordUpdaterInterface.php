<?php

namespace Webstack\UserBundle\Util;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface PasswordUpdaterInterface
 */
interface PasswordUpdaterInterface
{
    /**
     * @param UserInterface $user
     */
    public function hashPassword(UserInterface $user): void;
}
