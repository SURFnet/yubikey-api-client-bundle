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

namespace Surfnet\YubikeyApiClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('surfnet_yubikey_api_client');
        $rootNode
            ->children()
                ->arrayNode('credentials')
                    ->info('YubiKey API Credentials')
                    ->children()
                        ->scalarNode('client_id')
                            ->info('Client ID for the YubiKey API')
                            ->isRequired()
                            ->validate()
                                ->ifTrue(function ($value) {
                                    return (!is_string($value) && !is_int($value)) || trim($value) === '';
                                })
                                ->thenInvalid('Invalid YubiKey API Client ID specified: "%s"')
                            ->end()
                        ->end()
                        ->scalarNode('client_secret')
                            ->info('Secret for the YubiKey API')
                            ->isRequired()
                            ->validate()
                                ->ifTrue(function ($value) {
                                    return (!is_string($value) || trim($value) === '');
                                })
                                ->thenInvalid('Invalid YubiKey API secret specified: "%s"')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
