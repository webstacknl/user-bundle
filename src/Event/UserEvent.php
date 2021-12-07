<?php

namespace Webstack\UserBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{
    protected Request $request;
    protected UserInterface $user;

    public function __construct(UserInterface $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
