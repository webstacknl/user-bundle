<?php

namespace Webstack\UserBundle\Util;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
