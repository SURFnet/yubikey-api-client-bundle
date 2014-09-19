<?php

namespace Surfnet\YubikeyApiClient\Tests\Service;

use Surfnet\YubikeyApiClient\Service\Otp;

class OtpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider otpStrings
     * @param string $string
     */
    public function testItParsesFromString($string, $otpString, $password, $publicId, $cipherText)
    {
        $otp = Otp::fromString($string);

        $this->assertSame($otpString, $otp->otp);
        $this->assertSame($password, $otp->password);
        $this->assertSame($publicId, $otp->publicId);
        $this->assertSame($cipherText, $otp->cipherText);
    }

    /**
     * @dataProvider otpStrings
     * @param string $string
     */
    public function testItValidatesCorrectOtps($string)
    {
        $this->assertTrue(Otp::isValid($string));
    }

    public function otpStrings()
    {
        return [
            'Regular OTP' => [
                'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                '',
                'ddddddbtbhnh',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
            'Password OTP' => [
                'passwd:ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                'passwd',
                'ddddddbtbhnh',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
            'Short public id' => [
                'vvvvvcjnkcfeiegrrnnednjcluulduerelthv',
                'vvvvvcjnkcfeiegrrnnednjcluulduerelthv',
                '',
                'vvvvv',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
            'Long public id' => [
                'ccccddddeeeeffffcjnkcfeiegrrnnednjcluulduerelthv',
                'ccccddddeeeeffffcjnkcfeiegrrnnednjcluulduerelthv',
                '',
                'ccccddddeeeeffff',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
            'Dvorak OTP' => [
                'jxe.uidchtnbpygkjxe.uidchtnbpygkjxe.uidchtnbpygk',
                'cbdefghijklnrtuvcbdefghijklnrtuvcbdefghijklnrtuv',
                '',
                'cbdefghijklnrtuv',
                'cbdefghijklnrtuvcbdefghijklnrtuv'
            ],
            'Dvorak OTP w/ password' => [
                'passwd:jxe.uidchtnbpygkjxe.uidchtnbpygkjxe.uidchtnbpygk',
                'cbdefghijklnrtuvcbdefghijklnrtuvcbdefghijklnrtuv',
                'passwd',
                'cbdefghijklnrtuv',
                'cbdefghijklnrtuvcbdefghijklnrtuv'
            ],
            'Mixed case OTP is lowercased' => [
                'ddddddbTBHNHCJNKCFEIEGRRnnednjclUULDUerelthv',
                'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                '',
                'ddddddbtbhnh',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
        ];
    }

    /**
     * @dataProvider nonStrings
     * @param mixed $nonString
     */
    public function testItValidatesGivenOtpIsAString($nonString)
    {
        $this->setExpectedException('Surfnet\YubikeyApiClient\Exception\InvalidArgumentException', 'not a string');

        Otp::fromString($nonString);
    }

    /**
     * @return array
     */
    public function nonStrings()
    {
        return [
            'integer' => [1],
            'float' => [1.1],
            'array' => [array()],
            'object' => [new \stdClass],
            'null' => [null],
            'boolean' => [false],
        ];
    }

    /**
     * @dataProvider nonOtpStrings
     * @param mixed $nonOtpString
     */
    public function testItValidatesGivenOtpIsAnOtpString($nonOtpString)
    {
        $this->setExpectedException('Surfnet\YubikeyApiClient\Exception\InvalidArgumentException', 'not a valid OTP');

        Otp::fromString($nonOtpString);
    }

    /**
     * @dataProvider nonOtpStrings
     * @param string $string
     */
    public function testItDoesntAcceptInvalidOtps($string)
    {
        $this->assertFalse(Otp::isValid($string));
    }

    public function nonOtpStrings()
    {
        return [
            'Has invalid characters' => ['abcdefghijklmnopqrstuvwxyz123456789'],
            'Too long' => [str_repeat('c', 100)],
            'Too short' => [str_repeat('c', 31)],
        ];
    }
}
