<?php

namespace Webstack\UserBundle\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface LoginManagerInterface
 */
interface LoginManagerInterface
{
    /**
     * @param string        $firewallName
     * @param UserInterface $user
     * @param Response|null $response
     */
    public function logInUser($firewallName, UserInterface $user, Response $response = null): void;
}
