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

namespace Surfnet\YubikeyApiClient\Service;

use Surfnet\YubikeyApiClient\Exception\InvalidArgumentException;

class Otp
{
    const OTP_REGEXP_QWERTY =
        '/^((.*):)?(([cbdefghijklnrtuvCBDEFGHIJKLNRTUV]{0,16})([cbdefghijklnrtuvCBDEFGHIJKLNRTUV]{32}))$/';
    const OTP_REGEX_DVORAK =
        '/^((.*):)?(([jxe.uidchtnbpygkJXE.UIDCHTNBPYGK]{0,16})([jxe.uidchtnbpygkJXE.UIDCHTNBPYGK]{32}))$/';

    /** @var string */
    public $otp;

    /** @var string */
    public $password;

    /** @var string */
    public $publicId;

    /** @var string */
    public $cipherText;

    /**
     * @param string $string
     * @return self
     * @throws InvalidArgumentException Thrown when the given string is not an OTP.
     */
    public static function fromString($string)
    {
        $otp = new self;

        if (preg_match(self::OTP_REGEXP_QWERTY, $string, $matches)) {
            $otp->otp = $matches[3];
        } elseif (preg_match(self::OTP_REGEX_DVORAK, $string, $matches)) {
            $otp->otp = strtr($matches[3], 'jxe.uidchtnbpygk', 'cbdefghijklnrtuv');
        } else {
            throw new InvalidArgumentException('Given string is not a valid OTP.');
        }

        $otp->password = $matches[2];
        $otp->publicId = $matches[4];
        $otp->cipherText = $matches[5];

        return $otp;
    }

    /**
     * @param string $string
     * @return bool
     */
    public static function isValidString($string)
    {
        return preg_match(self::OTP_REGEXP_QWERTY, $string, $matches)
            || preg_match(self::OTP_REGEXP_DVORAK, $string, $matches);
    }
}
