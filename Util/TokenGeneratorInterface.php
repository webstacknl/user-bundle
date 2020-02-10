<?php

namespace Webstack\UserBundle\Util;

/**
 * Interface TokenGeneratorInterface
 */
interface TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken(): string;
}
