<?php

namespace Surfnet\YubikeyApiClient\Tests\Crypto;

use Surfnet\YubikeyApiClient\Crypto\NonceGenerator;

class FixedNonceGenerator implements NonceGenerator
{
    /**
     * @var string
     */
    private $nonce;

    /**
     * @param string $nonce
     */
    public function __construct($nonce)
    {
        $this->nonce = $nonce;
    }

    public function generateNonce()
    {
        return $this->nonce;
    }
}
