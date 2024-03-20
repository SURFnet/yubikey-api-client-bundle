<?php

namespace Surfnet\YubikeyApiClientBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Surfnet\YubikeyApiClientBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    public function testClientIdIsRequired()
    {
        $this->assertCredentialsConfigurationIsInvalid([
            'client_secret' => '___',
        ], 'must be configured');
    }

    public function testClientIdMustBeNonEmptyString()
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

    public function testClientSecretIsRequired()
    {
        $this->assertCredentialsConfigurationIsInvalid([
            'client_id' => '38213',
        ], 'must be configured');
    }

    public function testClientSecretMustBeNonEmptyString()
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

    protected function assertCredentialsConfigurationIsInvalid(array $configurationValues, $expectedMessage = null): void
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
