<?php

namespace Surfnet\YubikeyApiClient\Crypto;

interface NonceGenerator
{
    /**
     * @return string
     */
    public function generateNonce();
}
