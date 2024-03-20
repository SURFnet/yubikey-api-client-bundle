<?php

namespace Surfnet\YubikeyApiClientBundle\Service;

use Psr\Log\LoggerInterface;
use Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException;
use Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException;
use Surfnet\YubikeyApiClient\Otp;
use Surfnet\YubikeyApiClient\Service\OtpVerificationResult;
use Surfnet\YubikeyApiClient\Service\VerificationServiceInterface as Service;

class VerificationService
{
    public function __construct(
        private Service $service,
        private LoggerInterface $logger,
    ) {
    }

    public function verify(Otp $otp): OtpVerificationResult
    {
        try {
            $result = $this->service->verify($otp);
        } catch (UntrustedSignatureException $e) {
            $this->logger->alert(sprintf('Yubico responded with invalid signature (%s)', $e->getMessage()), [
                'exception' => $e,
                'otp' => $otp->otp,
            ]);

            return new OtpVerificationResult(OtpVerificationResult::ERROR_BAD_SIGNATURE);
        } catch (RequestResponseMismatchException $e) {
            $this->logger->alert(sprintf('Yubico request and response didn\'t match (%s)', $e->getMessage()), [
                'exception' => $e,
                'otp' => $otp->otp,
            ]);

            return new OtpVerificationResult(OtpVerificationResult::ERROR_BACKEND_ERROR);
        }

        if ($result->isSuccessful()) {
            return $result;
        }

        $this->logger->critical(sprintf('Yubico responded with error status \'%s\'', $result->getError()), [
            'otp' => $otp->otp,
        ]);

        return $result;
    }
}
