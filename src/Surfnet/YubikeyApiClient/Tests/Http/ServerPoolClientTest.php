<?php

namespace Surfnet\YubikeyApiClient\Tests\Http;

use GuzzleHttp\Exception\RequestException;
use Mockery as m;
use Surfnet\YubikeyApiClient\Http\ServerPoolClient;

class ServerPoolClientTest extends \PHPUnit_Framework_TestCase
{
    public function testItTriesOnce()
    {
        $guzzleClient = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('get')->once()->andReturn(
                m::mock('GuzzleHttp\Message\ResponseInterface')
            )
            ->getMock();

        $client = new ServerPoolClient($guzzleClient);

        $client->get([]);
    }

    public function testItTriesTwice()
    {
        $returnValues = [
            new RequestException('Comms failure', m::mock('GuzzleHttp\Message\RequestInterface'), /*No response*/ null),
            m::mock('GuzzleHttp\Message\ResponseInterface'),
        ];

        $client = new ServerPoolClient(
            m::mock('GuzzleHttp\ClientInterface')
                ->shouldReceive('get')->twice()->andReturnUsing(function () use (&$returnValues) {
                    return array_shift($returnValues);
                })
                ->getMock()
        );

        $client->get([]);
    }

    public function testItThrowsGuzzlesExceptionAfterTryingTwice()
    {
        $this->setExpectedException('GuzzleHttp\Exception\RequestException', 'Comms failure #2');

        $exceptions = [
            new RequestException('Comms failure #1', m::mock('GuzzleHttp\Message\RequestInterface'), null),
            new RequestException('Comms failure #2', m::mock('GuzzleHttp\Message\RequestInterface'), null),
        ];

        $client = new ServerPoolClient(
            m::mock('GuzzleHttp\ClientInterface')
                ->shouldReceive('get')->twice()->andReturnUsing(function () use (&$exceptions) {
                    throw array_shift($exceptions);
                })
                ->getMock()
        );

        $client->get([]);
    }

    public function testItThrowsGuzzlesExceptionWhenItHasAResponse()
    {
        $this->setExpectedException('GuzzleHttp\Exception\RequestException', 'Internal server error');

        $client = new ServerPoolClient(
            m::mock('GuzzleHttp\ClientInterface')
                ->shouldReceive('get')->twice()->andThrow(
                    new RequestException(
                        'Internal server error',
                        m::mock('GuzzleHttp\Message\RequestInterface'),
                        m::mock('GuzzleHttp\Message\ResponseInterface')
                            ->shouldReceive('getStatusCode')->andReturn(500)
                            ->getMock()
                    )
                )
                ->getMock()
        );

        $client->get([]);
    }
}
