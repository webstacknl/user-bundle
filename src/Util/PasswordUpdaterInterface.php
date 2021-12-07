<?php

namespace Webstack\UserBundle\Util;

use Symfony\Component\Security\Core\User\UserInterface;

interface PasswordUpdaterInterface
{
    public function hashPassword(UserInterface $user): void;
}
