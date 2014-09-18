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

namespace Surfnet\YubikeyApiClient\Signing;

use Surfnet\YubikeyApiClient\Exception\InvalidArgumentException;

class Signer
{
    /**
     * @var array Valid parameters in the response message
     */
    private static $validResponseParams = [
        'nonce',
        'otp',
        'sessioncounter',
        'sessionuse',
        'sl',
        'status',
        't',
        'timeout',
        'timestamp'
    ];

    /**
     * @var string The base64-decoded client secret
     */
    private $clientSecret;

    /**
     * @param string $clientSecret The base64-encoded client secret
     */
    public function __construct($clientSecret)
    {
        if (!is_string($clientSecret)) {
            throw new InvalidArgumentException('Client secret must be string.');
        }

        $this->clientSecret = base64_decode($clientSecret);
    }

    /**
     * @param array $data
     * @return array
     */
    public function sign(array $data)
    {
        ksort($data);

        $queryString = $this->buildQueryString($data);
        $data['h'] = base64_encode(hash_hmac('sha1', $queryString, $this->clientSecret, true));

        return $data;
    }

    public function verifySignature(array $data)
    {
        $signedData = array_intersect_key($data, array_flip(static::$validResponseParams));
        ksort($signedData);

        $queryString = $this->buildQueryString($signedData);
        $signature = base64_encode(hash_hmac('sha1', $queryString, $this->clientSecret, true));

        return $data['h'] === $signature;
    }

    /**
     * @param array $query
     * @return string
     */
    private function buildQueryString(array $query)
    {
        $queryString = '';

        foreach ($query as $key => $value) {
            $queryString .= '&' . sprintf('%s=%s', $key, $value);
        }

        return substr($queryString, 1);
    }
}
