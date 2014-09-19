<?php

namespace Surfnet\YubikeyApiClient\Tests\Signing;

use Surfnet\YubikeyApiClient\Signing\Signer;

class SignerTest extends \PHPUnit_Framework_TestCase
{
    public function testItSignsData()
    {
        $signer = new Signer(base64_encode('surfnet'));
        $signedData = $signer->sign(['otp' => '1234']);

        $this->assertSame(['otp' => '1234', 'h' => 'AxRja+fRxnocSbsXKz0LXEOBCjw='], $signedData);
    }

    public function testItVerifiesSignature()
    {
        $signer = new Signer(base64_encode('surfnet'));
        $signedData = ['otp' => '1234', 'h' => 'AxRja+fRxnocSbsXKz0LXEOBCjw='];

        $this->assertTrue($signer->verifySignature($signedData));
    }

    public function testSignatureVerficationIgnoresUnknownResponseParams()
    {
        $signer = new Signer(base64_encode('surfnet'));
        $signedData = ['otp' => '1234', 'UNKNOWN' => 'PARAM', 'h' => 'AxRja+fRxnocSbsXKz0LXEOBCjw='];

        $this->assertTrue($signer->verifySignature($signedData));
    }

    /**
     * @dataProvider nonStrings
     * @param mixed $nonString
     */
    public function testClientSecretMustBeString($nonString)
    {
        $this->setExpectedException('Surfnet\YubikeyApiClient\Exception\InvalidArgumentException');

        new Signer($nonString);
    }

    /**
     * @return array
     */
    public function nonStrings()
    {
        return [
            'integer' => [1],
            'float' => [1.1],
            'array' => [array()],
            'object' => [new \stdClass],
            'null' => [null],
            'boolean' => [false],
        ];
    }

    /**
     * @dataProvider nonBase64DecodableStrings
     * @param mixed $nonBase64DecodableString
     */
    public function testClientSecretMustBeBase64DecodableString($nonBase64DecodableString)
    {
        $this->setExpectedException('Surfnet\YubikeyApiClient\Exception\InvalidArgumentException');

        new Signer($nonBase64DecodableString);
    }

    public function nonBase64DecodableStrings()
    {
        return [
            ['W*()$&#*($&)'],
            ['P:}{<>?>,'],
            ['   d89d'],
        ];
    }
}
