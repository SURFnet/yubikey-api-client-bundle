<?php

declare(strict_types = 1);

namespace Surfnet\YubikeyApiClientBundle\Tests\Service;

use PHPUnit\Framework\Attributes\DataProvider;
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
    public function testItVerifiesAnOtp(): void
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');

        $result = $this->createMock(OtpVerificationResult::class);
        $result->method('isSuccessful')->willReturn(true);

        $verificationService = $this->createMock(VerificationServiceInterface::class);
        $verificationService->expects($this->once())
            ->method('verify')
            ->with($otp)
            ->willReturn($result);

        $logger = $this->createMock(LoggerInterface::class);

        $service = new VerificationService($verificationService, $logger);

        $this->assertTrue($service->verify($otp)->isSuccessful());
    }

    public function testItLogsUntrustedSignaturesAsAlerts(): void
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');

        $verificationService = $this->createMock(VerificationServiceInterface::class);
        $verificationService->expects($this->once())
            ->method('verify')
            ->with($otp)
            ->willThrowException(new UntrustedSignatureException());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('alert');

        $service = new VerificationService($verificationService, $logger);

        $result = $service->verify($otp);
        $this->assertEquals(OtpVerificationResult::ERROR_BAD_SIGNATURE, $result->getError());
    }

    public function testItLogsRequestResponseMismatchesAsAlerts(): void
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');

        $verificationService = $this->createMock(VerificationServiceInterface::class);
        $verificationService->expects($this->once())
            ->method('verify')
            ->with($otp)
            ->willThrowException(new RequestResponseMismatchException());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('alert');

        $service = new VerificationService($verificationService, $logger);

        $result = $service->verify($otp);
        $this->assertEquals(OtpVerificationResult::ERROR_BACKEND_ERROR, $result->getError());
    }

    #[DataProvider('criticalErrorStatuses')]
    public function testItLogsAllOtherErrorStatusesAsCriticals(string $errorStatus): void
    {
        $otp = Otp::fromString('ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv');
        $result = $this->createMock(OtpVerificationResult::class);
        $result->method('isSuccessful')->willReturn(false);
        $result->method('getError')->willReturn($errorStatus);

        $verificationService = $this->createMock(VerificationServiceInterface::class);
        $verificationService->expects($this->once())
            ->method('verify')
            ->with($otp)
            ->willReturn($result);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical');

        $service = new VerificationService($verificationService, $logger);

        $service->verify($otp);
    }

    /**
     * @return array<string,array<string>>
     */
    public static function criticalErrorStatuses(): array
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
