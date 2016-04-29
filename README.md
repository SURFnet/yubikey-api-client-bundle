# Yubikey API Client Bundle

|               | Build Status  | Scrutinizer Code Quality |
| ------------- | ------------- | ----- |
| develop       | [![Build Status](https://travis-ci.org/SURFnet/yubikey-api-client-bundle.svg?branch=develop)](https://travis-ci.org/SURFnet/yubikey-api-client-bundle) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SURFnet/yubikey-api-client-bundle/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/SURFnet/yubikey-api-client-bundle/?branch=develop) |
| master       | [![Build Status](https://travis-ci.org/SURFnet/yubikey-api-client-bundle.svg?branch=master)](https://travis-ci.org/SURFnet/yubikey-api-client-bundle) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SURFnet/yubikey-api-client-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SURFnet/yubikey-api-client-bundle/?branch=master) |

A Symfony2 bundle to integrate Yubikey's OTP validation service.

## Installation

Add the bundle to your Composer file.

```sh
composer require 'surfnet/yubikey-api-client-bundle'
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
    
    if (!\Surfnet\YubikeyApiClient\Otp::isValid('user-input-otp-here')) {
        // User-entered OTP string is not valid.
    }
    
    $otp = \Surfnet\YubikeyApiClient\Otp::fromString('user-input-otp-here');
    $result = $service->verify($otp);
    
    if ($result->isSuccessful()) {
        // Yubico verified OTP.
    }
}
```
