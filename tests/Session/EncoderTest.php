<?php

namespace Jasny\Session;

use PHPUnit\Framework\TestCase;
use Jasny\Session\Encoder;

/**
 * @covers Jasny\Session\Encoder
 */
class EncoderTest extends TestCase
{
    /**
     * @var Encoder
     */
    private $encoder;

    public function setUp()
    {
        parent::setUp();

        $this->encoder = new Encoder();
    }

    public function provideEncodeAndExpectedDecodedData() : array
    {
        return [
            [
                [],
                '',
            ],
            [
                ['counter' => 0],
                'counter|i:0;',
            ],
            [
                [
                    'product_code' => '2222',
                    'logged_in' => 'yes',
                ],
                'product_code|s:4:"2222";logged_in|s:3:"yes";',
            ],
            [
                [
                    'login_ok' => true,
                    'name'     => 'sica',
                    'integer'  => 34,
                    'obj'      => (object) [
                        'heya' => 123,
                    ],
                ],
                'login_ok|b:1;name|s:4:"sica";integer|i:34;obj|O:8:"stdClass":1:{s:4:"heya";i:123;}',
            ],
        ];
    }
    
    /**
     * @dataProvider provideEncodeAndExpectedDecodedData
     */
    public function testInvoke(array $encodedString, $expected)
    {
        $actual = $this->encoder->__invoke($encodedString);

        self::assertEquals($expected, $actual);
    }
}
