<?php

namespace Surfnet\YubikeyApiClientBundle\Tests\Service;

use Mockery as m;
use Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException;
use Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException;
use Surfnet\YubikeyApiClient\Otp;
use Surfnet\YubikeyApiClient\Service\OtpVerificationResult;
use Surfnet\YubikeyApiClientBundle\Service\VerificationService;

class VerificationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testItVerifiesAnOtp()
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');
        $result = m::mock('Surfnet\YubikeyApiClient\Service\VerifyOtpResult')
            ->shouldReceive('isSuccessful')->twice()->andReturn(true)
            ->getMock();

        $service = new VerificationService(
            m::mock('Surfnet\YubikeyApiClient\Service\VerificationService')
                ->shouldReceive('verify')->once()->with($otp)->andReturn($result)
                ->getMock(),
            m::mock('Psr\Log\LoggerInterface')
        );

        $this->assertTrue($service->verify($otp)->isSuccessful());
    }

    public function testItLogsUntrustedSignaturesAsAlerts()
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');

        $service = new VerificationService(
            m::mock('Surfnet\YubikeyApiClient\Service\VerificationService')
                ->shouldReceive('verify')->once()->with($otp)->andThrow(new UntrustedSignatureException)
                ->getMock(),
            m::mock('Psr\Log\LoggerInterface')
                ->shouldReceive('alert')->once()->andReturnUsing(
                    $this->logMessageContains('invalid signature')
                )
                ->getMock()
        );

        $result = $service->verify($otp);
        $this->assertEquals(OtpVerificationResult::ERROR_BAD_SIGNATURE, $result->getError());
    }

    public function testItLogsRequestResponseMismatchesAsAlerts()
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');

        $service = new VerificationService(
            m::mock('Surfnet\YubikeyApiClient\Service\VerificationService')
                ->shouldReceive('verify')->once()->with($otp)->andThrow(new RequestResponseMismatchException)
                ->getMock(),
            m::mock('Psr\Log\LoggerInterface')
                ->shouldReceive('alert')->once()->andReturnUsing(
                    $this->logMessageContains('request and response didn\'t match')
                )
                ->getMock()
        );

        $result = $service->verify($otp);
        $this->assertEquals(OtpVerificationResult::ERROR_BACKEND_ERROR, $result->getError());
    }

    /**
     * @dataProvider criticalErrorStatuses
     * @param string $errorStatus
     */
    public function testItLogsAllOtherErrorStatusesAsCriticals($errorStatus)
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');
        $result = m::mock('Surfnet\YubikeyApiClient\Service\VerifyOtpResult')
            ->shouldReceive('isSuccessful')->once()->andReturn(false)
            ->shouldReceive('getError')->once()->andReturn($errorStatus)
            ->getMock();

        $service = new VerificationService(
            m::mock('Surfnet\YubikeyApiClient\Service\VerificationService')
                ->shouldReceive('verify')->once()->with($otp)->andReturn($result)
                ->getMock(),
            m::mock('Psr\Log\LoggerInterface')
                ->shouldReceive('critical')->once()->andReturnUsing(
                    $this->logMessageContains('responded with error status')
                )
                ->getMock()
        );

        $service->verify($otp);
    }

    public function criticalErrorStatuses()
    {
        return [
            'Didn\'t log ERROR_BAD_OTP as critical'               => [OtpVerificationResult::ERROR_BAD_OTP],
            'Didn\'t log ERROR_REPLAYED_OTP as critical'          => [OtpVerificationResult::ERROR_REPLAYED_OTP],
            'Didn\'t log ERROR_BAD_SIGNATURE as critical'         => [OtpVerificationResult::ERROR_BAD_SIGNATURE],
            'Didn\'t log ERROR_MISSING_PARAMETER as critical'     => [OtpVerificationResult::ERROR_MISSING_PARAMETER],
            'Didn\'t log ERROR_NO_SUCH_CLIENT as critical'        => [OtpVerificationResult::ERROR_NO_SUCH_CLIENT],
            'Didn\'t log ERROR_OPERATION_NOT_ALLOWED as critical' => [OtpVerificationResult::ERROR_OPERATION_NOT_ALLOWED],
            'Didn\'t log ERROR_BACKEND_ERROR as critical'         => [OtpVerificationResult::ERROR_BACKEND_ERROR],
            'Didn\'t log ERROR_NOT_ENOUGH_ANSWERS as critical'    => [OtpVerificationResult::ERROR_NOT_ENOUGH_ANSWERS],
            'Didn\'t log ERROR_REPLAYED_REQUEST as critical'      => [OtpVerificationResult::ERROR_REPLAYED_REQUEST],
        ];
    }

    private function logMessageContains($contains)
    {
        return function ($string) use ($contains) {
            if (!is_string($string)) {
                throw new \Exception('Expected log message to be string');
            }

            if (strpos($string, $contains) === false) {
                throw new \Exception(sprintf('Unexpected log message: "%s"', $string));
            }
        };
    }
}
