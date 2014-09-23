<?php

namespace Surfnet\YubikeyApiClient\Tests\Crypto;

use Surfnet\YubikeyApiClient\Crypto\RandomNonceGenerator;

class RandomNonceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testItGeneratesMd5Nonce()
    {
        $generator = new RandomNonceGenerator;
        $nonce = $generator->generateNonce();

        $this->assertSame(1, preg_match('/^[a-f0-9]{32}$/', $nonce));
    }
}
