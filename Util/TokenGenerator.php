<?php

namespace Webstack\UserBundle\Util;

use Exception;

/**
 * Class TokenGenerator
 */
class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
