<?php

namespace Surfnet\YubikeyApiClient\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Message\ResponseInterface;

class ServerPoolClient
{
    private static $serverPool = [
        'http://api.yubico.com/wsapi/2.0/verify',
        'http://api2.yubico.com/wsapi/2.0/verify',
        'http://api3.yubico.com/wsapi/2.0/verify',
        'http://api4.yubico.com/wsapi/2.0/verify',
        'http://api5.yubico.com/wsapi/2.0/verify',
    ];

    /**
     * @var ClientInterface
     */
    private $guzzleClient;

    /**
     * @param ClientInterface $guzzleClient
     */
    public function __construct(ClientInterface $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param array $requestOptions
     * @return ResponseInterface
     * @throws TransferException
     */
    public function get(array $requestOptions)
    {
        try {
            $poolIndex = array_rand(self::$serverPool);
            return $this->guzzleClient->get(self::$serverPool[$poolIndex], $requestOptions);
        } catch (RequestException $e) {
            if ($e->getResponse()) {
                throw $e;
            }
        }

        // There is no server response (timeout, DNS failure); try again.
        $poolIndex = ($poolIndex + 1) % count(self::$serverPool);
        return $this->guzzleClient->get(self::$serverPool[$poolIndex], $requestOptions);
    }
}
