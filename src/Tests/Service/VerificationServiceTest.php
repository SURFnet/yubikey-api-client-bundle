<?php

namespace Surfnet\YubikeyApiClientBundle\Tests\Service;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException;
use Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException;
use Surfnet\YubikeyApiClient\Otp;
use Surfnet\YubikeyApiClient\Service\OtpVerificationResult;
use Surfnet\YubikeyApiClientBundle\Service\VerificationService;
use Surfnet\YubikeyApiClient\Service\VerificationServiceInterface;

class VerificationServiceTest extends TestCase
{
    public function testItVerifiesAnOtp()
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');
        $result = m::mock(OtpVerificationResult::class)
            ->shouldReceive('isSuccessful')->andReturn(true)
            ->getMock();

        $service = new VerificationService(
            m::mock(VerificationServiceInterface::class)
                ->shouldReceive('verify')->once()->with($otp)->andReturn($result)
                ->getMock(),
            m::mock(LoggerInterface::class)
        );

        $this->assertTrue($service->verify($otp)->isSuccessful());
    }

    public function testItLogsUntrustedSignaturesAsAlerts()
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');

        $service = new VerificationService(
            m::mock(VerificationServiceInterface::class)
                ->shouldReceive('verify')->once()->with($otp)->andThrow(new UntrustedSignatureException)
                ->getMock(),
            m::mock(LoggerInterface::class)
                ->shouldReceive('alert')->once()
                ->getMock()
        );

        $result = $service->verify($otp);
        $this->assertEquals(OtpVerificationResult::ERROR_BAD_SIGNATURE, $result->getError());
    }

    public function testItLogsRequestResponseMismatchesAsAlerts()
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');

        $service = new VerificationService(
            m::mock(VerificationServiceInterface::class)
                ->shouldReceive('verify')->once()->with($otp)->andThrow(new RequestResponseMismatchException)
                ->getMock(),
            m::mock(LoggerInterface::class)
                ->shouldReceive('alert')->once()
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
        $result = m::mock(OtpVerificationResult::class)
            ->shouldReceive('isSuccessful')->once()->andReturn(false)
            ->shouldReceive('getError')->once()->andReturn($errorStatus)
            ->getMock();

        $service = new VerificationService(
            m::mock(VerificationServiceInterface::class)
                ->shouldReceive('verify')->once()->with($otp)->andReturn($result)
                ->getMock(),
            m::mock(LoggerInterface::class)
                ->shouldReceive('critical')->once()
                ->getMock()
        );

        $service->verify($otp);
        $this->expectNotToPerformAssertions();
    }

    public function criticalErrorStatuses(): array
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
}
