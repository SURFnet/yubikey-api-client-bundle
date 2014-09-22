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
use GuzzleHttp\Exception\RequestException;
use Surfnet\YubikeyApiClient\Crypto\NonceGenerator;
use Surfnet\YubikeyApiClient\Crypto\Signer;
use Surfnet\YubikeyApiClient\Exception\InvalidArgumentException;
use Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException;
use Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException;
use Surfnet\YubikeyApiClient\Otp;

class VerificationService
{
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
     * @return OtpVerificationResult
     * @throws UntrustedSignatureException When the signature doesn't match the expected signature.
     * @throws RequestResponseMismatchException When the response data doesn't match the requested data (otp, nonce).
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

        try {
            $serverIndex = array_rand(self::$servers);
            $httpResponse = $this->guzzle->get(self::$servers[$serverIndex], ['query' => $query]);
        } catch (RequestException $e) {
            if ($e->getResponse()) {
                throw $e;
            }

            // There is no server response (timeout, DNS failure); try again.
            $serverIndex = ($serverIndex + 1) % count(self::$servers);
            $httpResponse = $this->guzzle->get(self::$servers[$serverIndex], ['query' => $query]);
        }

        $response = $this->parseYubicoResponse((string) $httpResponse->getBody());

        if (!$this->signer->verifySignature($response)) {
            throw new UntrustedSignatureException('The response data signature doesn\'t match the expected signature.');
        }

        if ($response['otp'] !== $otp->otp) {
            throw new RequestResponseMismatchException('The response OTP doesn\'t match the requested OTP.');
        }

        if ($response['nonce'] !== $nonce) {
            throw new RequestResponseMismatchException('The response nonce doesn\'t match the requested nonce.');
        }

        return new OtpVerificationResult($response['status']);
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
