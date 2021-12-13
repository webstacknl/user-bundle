<?php

namespace Webstack\UserBundle\Event;

use Symfony\Component\HttpFoundation\Response;

class GetResponseUserEvent extends UserEvent
{
    private Response $response;

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}