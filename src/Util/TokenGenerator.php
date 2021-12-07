<?php

namespace Webstack\UserBundle\Util;

use Exception;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @throws Exception
     */
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
