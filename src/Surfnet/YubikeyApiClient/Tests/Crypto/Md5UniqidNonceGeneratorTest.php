<?php

namespace Surfnet\YubikeyApiClient\Tests\Crypto;

use Surfnet\YubikeyApiClient\Crypto\Md5UniqidNonceGenerator;

class Md5UniqidNonceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testItGeneratesMd5Nonce()
    {
        $generator = new Md5UniqidNonceGenerator;
        $nonce = $generator->generateNonce();

        $this->assertSame(1, preg_match('/^[a-f0-9]{32}$/', $nonce));
    }
}
