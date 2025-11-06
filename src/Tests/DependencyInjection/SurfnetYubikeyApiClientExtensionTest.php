<?php

declare(strict_types = 1);

namespace Surfnet\YubikeyApiClientBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Surfnet\YubikeyApiClientBundle\Tests\TestKernel;
use Symfony\Component\DependencyInjection\Container;

class SurfnetYubikeyApiClientExtensionTest extends TestCase
{
    #[Test]
    #[Group('DependencyInjection')]
    public function verificationServiceCanBeLoaded(): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();

        /** @var Container $container */
        $container = $kernel->getContainer();
        $this->expectNotToPerformAssertions();
        $container->get('surfnet_yubikey_api_client.verification_service');
    }
}
