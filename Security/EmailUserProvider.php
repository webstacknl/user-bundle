<?php

namespace Webstack\UserBundle\Security;

/**
 * Class EmailUserProvider
 */
class EmailUserProvider extends UserProvider
{
    /**
     * {@inheritdoc}
     */
    protected function findUser($email)
    {
        return $this->userManager->findUser($email);
    }
}
