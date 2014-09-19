<?php

/**
 * Copyright 2014 SURFnet bv
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

namespace Surfnet\YubikeyApiClient\Service;

use GuzzleHttp\ClientInterface;
use Surfnet\YubikeyApiClient\Crypto\NonceGenerator;
use Surfnet\YubikeyApiClient\Exception\InvalidArgumentException;
use Surfnet\YubikeyApiClient\Exception\InvalidResponseException;
use Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException;
use Surfnet\YubikeyApiClient\Crypto\Signer;

class VerificationService
{
    /** The OTP is valid. */
    const STATUS_OK = 'OK';
    /** The OTP is invalid format. */
    const STATUS_BAD_OTP = 'BAD_OTP';
    /** The OTP has already been seen by the service. */
    const STATUS_REPLAYED_OTP = 'REPLAYED_OTP';
    /** The HMAC signature verification failed. */
    const STATUS_BAD_SIGNATURE = 'BAD_SIGNATURE';
    /** The request lacks a parameter. */
    const STATUS_MISSING_PARAMETER = 'MISSING_PARAMETER';
    /** The request id does not exist. */
    const STATUS_NO_SUCH_CLIENT = 'NO_SUCH_CLIENT';
    /** The request id is not allowed to verify OTPs. */
    const STATUS_OPERATION_NOT_ALLOWED = 'OPERATION_NOT_ALLOWED';
    /** Unexpected error in our server. Please contact us if you see this error. */
    const STATUS_BACKEND_ERROR = 'BACKEND_ERROR';
    /** Server could not get requested number of syncs during before timeout */
    const STATUS_NOT_ENOUGH_ANSWERS = 'NOT_ENOUGH_ANSWERS';
    /** Server has seen the OTP/Nonce combination before */
    const STATUS_REPLAYED_REQUEST = 'REPLAYED_REQUEST';

    private static $servers = [
        'http://api.yubico.com/wsapi/2.0/verify',
        'http://api2.yubico.com/wsapi/2.0/verify',
        'http://api3.yubico.com/wsapi/2.0/verify',
        'http://api4.yubico.com/wsapi/2.0/verify',
        'http://api5.yubico.com/wsapi/2.0/verify',
    ];

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var string Yubico client ID
     */
    private $clientId;

    /**
     * @var NonceGenerator
     */
    private $nonceGenerator;

    /**
     * @param ClientInterface $guzzle
     * @param NonceGenerator $nonceGenerator
     * @param Signer $signer
     * @param string $clientId
     */
    public function __construct(ClientInterface $guzzle, NonceGenerator $nonceGenerator, Signer $signer, $clientId)
    {
        if (!is_string($clientId)) {
            throw new InvalidArgumentException('Client ID must be string.');
        }

        $this->guzzle = $guzzle;
        $this->signer = $signer;
        $this->clientId = $clientId;
        $this->nonceGenerator = $nonceGenerator;
    }

    /**
     * @param Otp $otp
     * @return string A Yubico response status. See the STATUS_* constants.
     * @throws UntrustedSignatureException When the signature doesn't match the expected signature.
     * @throws InvalidResponseException When the response data doesn't match the requested data (otp, nonce).
     */
    public function verify(Otp $otp)
    {
        $nonce = $this->nonceGenerator->generateNonce();

        $query = [
            'id'    => $this->clientId,
            'otp'   => $otp->otp,
            'nonce' => $nonce,
        ];
        $query = $this->signer->sign($query);

        $verificationServerUrl = self::$servers[array_rand(self::$servers)];
        $httpResponse = $this->guzzle->get($verificationServerUrl, ['query' => $query]);
        $response = $this->parseYubicoResponse((string) $httpResponse->getBody());

        if (!$this->signer->verifySignature($response)) {
            throw new UntrustedSignatureException('The response data signature doesn\'t match the expected signature.');
        }

        if ($response['otp'] !== $otp->otp) {
            throw new InvalidResponseException('The response OTP doesn\'t match the requested OTP.');
        }

        if ($response['nonce'] !== $nonce) {
            throw new InvalidResponseException('The response nonce doesn\'t match the requested nonce.');
        }

        return $response['status'];
    }

    /**
     * Parses the response.
     *
     * @param string $response
     * @return array
     */
    private function parseYubicoResponse($response)
    {
        $lines = array_filter(explode("\r\n", $response));
        $responseArray = array();

        foreach ($lines as $line) {
            list($key, $value) = explode('=', $line, 2);

            $responseArray[$key] = $value;
        }

        return $responseArray;
    }
}
