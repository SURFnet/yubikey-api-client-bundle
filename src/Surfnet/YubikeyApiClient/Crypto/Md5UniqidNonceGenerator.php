<?php

namespace Surfnet\YubikeyApiClient\Crypto;

class Md5UniqidNonceGenerator implements NonceGenerator
{
    public function generateNonce()
    {
        return md5(uniqid(rand()));
    }
}
