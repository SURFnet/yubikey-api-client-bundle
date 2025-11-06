# Yubikey API Client Bundle

A Symfony bundle to integrate Yubikey's OTP validation service.

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
