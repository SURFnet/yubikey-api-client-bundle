<?php

namespace Surfnet\YubikeyApiClient\Crypto;

class RandomNonceGenerator implements NonceGenerator
{
    public function generateNonce()
    {
        return md5(uniqid(rand()));
    }
}
