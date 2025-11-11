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

namespace Surfnet\YubikeyApiClientBundle\DependencyInjection;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SurfnetYubikeyApiClientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();
        $configs = $processor->processConfiguration(new Configuration(), $configs);

        $credentials = $configs['credentials'];

        if (!is_array($credentials)) {
            throw new InvalidArgumentException('Invalid YubikeyApiClient credentials.');
        }

        $clientId = $credentials['client_id'];
        assert(is_string($clientId) || is_int($clientId));

        $secret = $credentials['client_secret'];
        assert(is_string($secret));

        $container->setParameter('surfnet_yubikey_api_client.credentials.client_id', (string)$clientId);
        $container->setParameter('surfnet_yubikey_api_client.credentials.client_secret', $secret);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        //check for test environment
        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('services_test.yml');
        }
    }
}
