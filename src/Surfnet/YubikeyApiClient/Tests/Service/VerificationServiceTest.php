<?php

namespace Surfnet\YubikeyApiClient\Tests\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;
use Mockery as m;
use Surfnet\YubikeyApiClient\Crypto\Signer;
use Surfnet\YubikeyApiClient\Service\VerificationService;
use Surfnet\YubikeyApiClient\Tests\Crypto\FixedNonceGenerator;

class VerificationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider nonStrings
     * @param mixed $nonString
     */
    public function testClientIdMustBeString($nonString)
    {
        $this->setExpectedException(
            'Surfnet\YubikeyApiClient\Exception\InvalidArgumentException',
            'Client ID must be string'
        );

        new VerificationService(
            m::mock('GuzzleHttp\ClientInterface'),
            m::mock('Surfnet\YubikeyApiClient\Crypto\NonceGenerator'),
            m::mock('Surfnet\YubikeyApiClient\Crypto\Signer'),
            $nonString
        );
    }

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

    public function testVerifiesOtp()
    {
        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse($otpString, $nonce);
        $guzzleClient = $this->createGuzzleClient($expectedResponse);
        $nonceGenerator = new FixedNonceGenerator('surfnet');
        $signer = $this->createDummySigner($expectedQuery, true);

        $otp = m::mock('Surfnet\YubikeyApiClient\Service\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($guzzleClient, $nonceGenerator, $signer, '1234');

        $this->assertEquals(VerificationService::STATUS_OK, $service->verify($otp));
    }

    public function testVerifiesResponseOtpEqualsRequestOtp()
    {
        $this->setExpectedException(
            'Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException',
            'OTP doesn\'t match'
        );

        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse('different OTP', $nonce);
        $guzzleClient = $this->createGuzzleClient($expectedResponse);
        $nonceGenerator = new FixedNonceGenerator('surfnet');
        $signer = $this->createDummySigner($expectedQuery, true);

        $otp = m::mock('Surfnet\YubikeyApiClient\Service\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($guzzleClient, $nonceGenerator, $signer, '1234');
        $service->verify($otp);
    }

    public function testVerifiesResponseNonceEqualsRequestNonce()
    {
        $this->setExpectedException(
            'Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException',
            'nonce doesn\'t match'
        );

        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse($otpString, 'different nonce');
        $guzzleClient = $this->createGuzzleClient($expectedResponse);
        $nonceGenerator = new FixedNonceGenerator('surfnet');
        $signer = $this->createDummySigner($expectedQuery, true);

        $otp = m::mock('Surfnet\YubikeyApiClient\Service\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($guzzleClient, $nonceGenerator, $signer, '1234');
        $service->verify($otp);
    }

    public function testVerifiesServerSignature()
    {
        $this->setExpectedException(
            'Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException',
            'signature doesn\'t match'
        );

        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse($otpString, $nonce);
        $guzzleClient = $this->createGuzzleClient($expectedResponse);
        $nonceGenerator = new FixedNonceGenerator('surfnet');
        $signer = $this->createDummySigner($expectedQuery, false);

        $otp = m::mock('Surfnet\YubikeyApiClient\Service\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($guzzleClient, $nonceGenerator, $signer, '1234');
        $service->verify($otp);
    }

    public function testItRetriesOnceOnServerCommunicationFailure()
    {
        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse($otpString, $nonce);
        $returnValues = [
            new RequestException('Server time-out', m::mock('GuzzleHttp\Message\RequestInterface')),
            $expectedResponse,
        ];
        $previousUrl = null;
        $guzzleClient = m::mock('GuzzleHttp\Client')
            ->shouldReceive('get')
                ->twice()
                ->andReturnUsing(function ($url) use (&$returnValues, &$previousUrl) {
                    if ($url === $previousUrl) {
                        throw new \Exception('VerificationService retried, but with same URL');
                    }

                    $previousUrl = $url;

                    $value = array_shift($returnValues);

                    if ($value instanceof \Exception) {
                        throw $value;
                    } else {
                        return $value;
                    }
                })
            ->getMock();
        $nonceGenerator = new FixedNonceGenerator('surfnet');
        $signer = $this->createDummySigner($expectedQuery, true);

        $otp = m::mock('Surfnet\YubikeyApiClient\Service\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($guzzleClient, $nonceGenerator, $signer, '1234');

        $this->assertEquals(VerificationService::STATUS_OK, $service->verify($otp));
    }

    /**
     * @param string $otpString
     * @param string $nonce
     * @return ResponseInterface
     */
    private function createVerificationResponse($otpString, $nonce)
    {
        $expectedResponse = m::mock('GuzzleHttp\Message\ResponseInterface')
            ->shouldReceive('getBody')->once()->andReturn("status=OK\r\notp=$otpString\r\nnonce=$nonce")
            ->getMock();

        return $expectedResponse;
    }

    /**
     * @param ResponseInterface $expectedResponse
     * @return ClientInterface
     */
    private function createGuzzleClient(ResponseInterface $expectedResponse)
    {
        $guzzleClient = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('get')->once()->andReturn($expectedResponse)
            ->getMock();

        return $guzzleClient;
    }

    /**
     * @param array $request
     * @param boolean $verifiesSignature
     * @return Signer
     */
    private function createDummySigner(array $request, $verifiesSignature)
    {
        $signer = m::mock('Surfnet\YubikeyApiClient\Crypto\Signer')
            ->shouldReceive('sign')->once()->with($request)->andReturn($request)
            ->shouldReceive('verifySignature')->once()->andReturn($verifiesSignature)
            ->getMock();

        return $signer;
    }
}
