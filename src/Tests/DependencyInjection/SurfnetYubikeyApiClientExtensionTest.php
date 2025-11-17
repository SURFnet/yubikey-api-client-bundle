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
