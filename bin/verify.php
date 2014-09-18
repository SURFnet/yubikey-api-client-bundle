#!/usr/bin/env php
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

$clientId = getenv('YUBIKEY_CLIENT_ID');
$clientSecret = getenv('YUBIKEY_CLIENT_SECRET');

if (empty($clientId)) {
    print "Please set the YUBIKEY_CLIENT_ID environment variable.\n";
    exit(-1);
}

if (empty($clientSecret)) {
    print "Please set the YUBIKEY_CLIENT_SECRET environment variable.\n";
    exit(-1);
}

if (count($argv) !== 2) {
    print "Please pass a Yubikey OTP as first argument (php bin/verify.php <press-yubikey>).\n";
    exit(-1);
}

$otpString = $argv[1];

require __DIR__ . '/../vendor/autoload.php';

$service = new \Surfnet\YubikeyApiClient\Service\VerificationService(
    new \GuzzleHttp\Client(),
    new \Surfnet\YubikeyApiClient\Signing\Signer($clientSecret),
    $clientId
);

$otp = \Surfnet\YubikeyApiClient\Service\Otp::fromString($otpString);
var_dump($service->verify($otp));
