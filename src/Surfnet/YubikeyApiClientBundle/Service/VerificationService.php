<?php

namespace Surfnet\YubikeyApiClientBundle\Service;

use Psr\Log\LoggerInterface;
use Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException;
use Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException;
use Surfnet\YubikeyApiClient\Service\Otp;
use Surfnet\YubikeyApiClient\Service\VerificationService as Service;

class VerificationService
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Service $service
     * @param LoggerInterface $logger
     */
    public function __construct(Service $service, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    public function verify(Otp $otp)
    {
        try {
            $status = $this->service->verify($otp);
        } catch (UntrustedSignatureException $e) {
            $this->logger->alert(sprintf('Yubico responded with invalid signature (%s)', $e->getMessage()), [
                'exception' => $e,
                'otp' => $otp->otp,
            ]);

            return Service::STATUS_BAD_SIGNATURE;
        } catch (RequestResponseMismatchException $e) {
            $this->logger->alert(sprintf('Yubico request and response didn\'t match (%s)', $e->getMessage()), [
                'exception' => $e,
                'otp' => $otp->otp,
            ]);

            return Service::STATUS_BACKEND_ERROR;
        }

        if ($status === Service::STATUS_OK) {
            return $status;
        }

        $this->logger->critical(sprintf('Yubico responded with error status \'%s\'', $status), [
            'otp' => $otp->otp,
        ]);
    }
}
