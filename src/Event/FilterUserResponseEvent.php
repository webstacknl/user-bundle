<?php

namespace Webstack\UserBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class FilterUserResponseEvent extends UserEvent
{
    public function __construct(UserInterface $user, Request $request, private Response $response)
    {
        parent::__construct($user, $request);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
