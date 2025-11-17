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

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Surfnet\YubikeyApiClientBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    public function testClientIdIsRequired(): void
    {
        $this->assertCredentialsConfigurationIsInvalid([
            'client_secret' => '___',
        ], 'must be configured');
    }

    public function testClientIdMustBeNonEmptyString(): void
    {
        $this->assertCredentialsConfigurationIsInvalid([
            'client_id' => '',
            'client_secret' => '___',
        ], 'Invalid YubiKey API Client ID specified');


        $this->assertCredentialsConfigurationIsInvalid([
            'client_id' => '  ',
            'client_secret' => '___',
        ], 'Invalid YubiKey API Client ID specified');
    }

    public function testClientSecretIsRequired(): void
    {
        $this->assertCredentialsConfigurationIsInvalid([
            'client_id' => '38213',
        ], 'must be configured');
    }

    public function testClientSecretMustBeNonEmptyString(): void
    {
        $this->assertCredentialsConfigurationIsInvalid([
            'client_id' => '8932',
            'client_secret' => '',
        ], 'Invalid YubiKey API secret specified');


        $this->assertCredentialsConfigurationIsInvalid([
            'client_id' => '3892',
            'client_secret' => '   ',
        ], 'Invalid YubiKey API secret specified');
    }

    /**
     * @param array<string,string> $configurationValues
     */
    protected function assertCredentialsConfigurationIsInvalid(array $configurationValues, ?string $expectedMessage = null): void
    {
        $this->assertConfigurationIsInvalid(
            ['surfnet_yubikey_api_client' => ['credentials' => $configurationValues]],
            $expectedMessage
        );
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration;
    }
}
