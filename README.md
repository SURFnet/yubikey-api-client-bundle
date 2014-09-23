# Yubikey API Client Bundle

[![Build Status](https://travis-ci.org/SURFnet/yubikey-api-client-bundle.svg)](https://travis-ci.org/SURFnet/yubikey-api-client-bundle) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SURFnet/yubikey-api-client-bundle/badges/quality-score.png?b=feature%2Fvalidate-otp)](https://scrutinizer-ci.com/g/SURFnet/yubikey-api-client-bundle/?branch=feature%2Fvalidate-otp) [![SensioLabs Insight](https://insight.sensiolabs.com/projects/ff8db7ec-e164-4fcf-a90b-16c02856d1d4/mini.png)](https://insight.sensiolabs.com/projects/ff8db7ec-e164-4fcf-a90b-16c02856d1d4)

A Symfony2 bundle to integrate Yubikey's OTP validation service.

## Installation

Add the bundle to your Composer file.

```sh
composer require 'surfnet/yubikey-api-client-bundle:dev-develop'
```

Add the bundle to your AppKernel.

```php
public function registerBundles()
{
    $bundles[] = new Surfnet\YubikeyApiClientBundle\SurfnetYubikeyApiClientBundle;
}
```

## Usage

```php
public function fooAction()
{
    /** @var \Surfnet\YubikeyApiClientBundle\Service\VerificationService */
    $service = $this->get('surfnet_yubikey_api_client.verification_service');
    
    $otp = \Surfnet\YubikeyApiClient\Service\Otp::fromString('user-input-otp-here');
    $result = $service->verify($otp);
    
    if ($result->isSuccessful()) {
        // Yubico verified OTP.
    }
}
```
