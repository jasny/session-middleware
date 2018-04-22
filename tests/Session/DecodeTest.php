<?php

namespace Jasny;

use PHPUnit\Framework\TestCase;
use Jasny\Session\Decoder;

/**
 * @covers Jasny\Session\Decoder
 */
class DecodeTest extends TestCase
{
    /**
     * @var Decoder
     */
    private $decoder;

    public function setUp()
    {
        parent::setUp();

        $this->decoder = new Decoder();
    }

    public function provideEncodeAndExpectedDecodedData() : array
    {
        return [
            [
                '',
                []
            ],
            [
                'counter|i:0;',
                ['counter' => 0]
            ],
            [
                'product_code|s:4:"2222";logged_in|s:3:"yes";',
                [
                    'product_code' => '2222',
                    'logged_in' => 'yes',
                ]
            ],
            [
                'login_ok|b:1;name|s:4:"sica";integer|i:34;obj|O:8:"stdClass":1:{s:4:"heya";i:123;}',
                [
                    'login_ok' => true,
                    'name'     => 'sica',
                    'integer'  => 34,
                    'obj'      => (object) [
                        'heya' => 123,
                    ],
                ]
            ],
        ];
    }
    
    /**
     * @dataProvider provideEncodeAndExpectedDecodedData
     */
    public function testInvoke(string $encodedString, $expected)
    {
        $actual = $this->decoder->__invoke($encodedString);

        $this->assertEquals($expected, $actual);
    }
}
