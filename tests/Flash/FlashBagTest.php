<?php

declare(strict_types=1);

namespace Jasny\Session\Tests\Flash;

use Jasny\PHPUnit\ExpectWarningTrait;
use Jasny\Session\Flash\Flash;
use Jasny\Session\Flash\FlashBag;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Session\Flash\Flash
 * @covers \Jasny\Session\Flash\FlashBag
 */
class FlashBagTest extends TestCase
{
    use ExpectWarningTrait;

    public function testFromSession()
    {
        $session = new \ArrayObject([
            'flash' => [
                ['type' => 'notice', 'message' => 'one', 'contentType' => 'text/html'],
                ['message' => 'two']
            ]
        ]);

        $baseBag = new FlashBag();
        $bag = $baseBag->withSession($session);

        $this->assertNotSame($baseBag, $bag);
        $this->assertArrayNotHasKey('flash', $session->getArrayCopy());

        $expected = [
            new Flash('notice', 'one', 'text/html'),
            new Flash('', 'two', 'text/plain'),
        ];

        $this->assertEquals($expected, $bag->getArrayCopy());

        $this->assertSame($bag, $bag->withSession($session));
        $this->assertEquals($expected, $bag->getArrayCopy());

        $entries = iterator_to_array($bag, true);
        $this->assertEquals($expected, $entries);
    }

    public function testWithInvalidData()
    {
        $session = new \ArrayObject([
            'flash' => 'foo'
        ]);

        $this->expectWarningMessage('Invalid flash session data');

        $bag = (new FlashBag())->withSession($session);
        $this->assertEquals([], $bag->getArrayCopy());

        $this->assertArrayNotHasKey('flash', $session->getArrayCopy());
    }

    public function testWithInvalidEntries()
    {
        $session = new \ArrayObject([
            'flash' => [
                ['abc'],
                ['foo' => 'bar'],
                ['message' => 'two'],
            ]
        ]);

        $this->expectNoticeMessage('Ignoring 2 invalid flash messages');
        $bag = (new FlashBag())->withSession($session);

        $this->assertArrayNotHasKey('flash', $session->getArrayCopy());

        $expected = [
            new Flash('', 'two', 'text/plain'),
        ];

        $this->assertEquals($expected, $bag->getArrayCopy());
    }

    public function testAdd()
    {
        $session = new \ArrayObject();
        $bag = (new FlashBag())->withSession($session);

        $expected = [
            ['type' => 'notice', 'message' => 'foo', 'contentType' => 'text/plain'],
            ['type' => 'warning', 'message' => 'bar', 'contentType' => 'text/html'],
        ];

        $bag->add('notice', 'foo');
        $bag->add('warning', 'bar', 'text/html');

        $this->assertArrayHasKey('flash', $session->getArrayCopy());
        $this->assertEquals($expected, $session['flash']);

        $this->assertEquals([], $bag->getArrayCopy());
    }

    public function testReissue()
    {
        $session = new \ArrayObject([
            'flash' => [
                ['type' => 'notice', 'message' => 'one', 'contentType' => 'text/html'],
                ['message' => 'two']
            ]
        ]);

        $bag = (new FlashBag())->withSession($session);

        $bag->add('notice', 'foo');
        $bag->add('warning', 'bar', 'text/html');

        $expected = [
            ['type' => 'notice', 'message' => 'one', 'contentType' => 'text/html'],
            ['type' => '', 'message' => 'two', 'contentType' => 'text/plain'],
            ['type' => 'notice', 'message' => 'foo', 'contentType' => 'text/plain'],
            ['type' => 'warning', 'message' => 'bar', 'contentType' => 'text/html'],
        ];

        $bag->reissue();

        $this->assertArrayHasKey('flash', $session->getArrayCopy());
        $this->assertEquals($expected, $session['flash']);

        $this->assertEquals([], $bag->getArrayCopy());
    }

    public function testClear()
    {
        $session = new \ArrayObject([
            'flash' => [
                ['type' => 'notice', 'message' => 'one', 'contentType' => 'text/html'],
                ['message' => 'two']
            ]
        ]);

        $bag = (new FlashBag())->withSession($session);

        $bag->add('notice', 'foo');
        $bag->add('warning', 'bar', 'text/html');

        $bag->clear();

        $this->assertArrayNotHasKey('flash', $session->getArrayCopy());
        $this->assertEquals([], $bag->getArrayCopy());
    }
}
