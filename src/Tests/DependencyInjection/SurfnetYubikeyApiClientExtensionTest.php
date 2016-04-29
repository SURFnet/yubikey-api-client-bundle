<?php

namespace Surfnet\YubikeyApiClientBundle\Tests\DependencyInjection;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\YubikeyApiClientBundle\Tests\TestKernel;
use Symfony\Component\DependencyInjection\Container;

class SurfnetYubikeyApiClientExtensionTest extends TestCase
{
    /**
     * @test
     * @group DependencyInjection
     */
    public function verification_service_can_be_loaded()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();

        /** @var Container $container */
        $container = $kernel->getContainer();
        $container->get('surfnet_yubikey_api_client.verification_service');
    }
}
