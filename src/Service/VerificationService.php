<?php

/**
 * Copyright 2025 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types = 1);

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
        private readonly Service $service,
        private readonly LoggerInterface $logger,
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
