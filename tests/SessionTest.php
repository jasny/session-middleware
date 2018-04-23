<?php

namespace Jasny\Session;

use PHPUnit\Framework\TestCase;
use Jasny\Session;
use Jasny\Session\Flash;
use Jasny\Session\FlashInterface;

/**
 * @covers Jasny\Session
 */
class SessionTest extends TestCase
{
    /**
     * @var Session
     */
    private $session;

    public function setUp()
    {
        parent::setUp();

        $this->session = new Session(['foo' => 'bar']);
    }
    
    public function testCreate()
    {
        $session = $this->session->create('12345', ['hello' => 'world']);
        
        $this->assertInstanceOf(Session::class, $session);
        $this->assertEquals('12345', $session->getId());
        $this->assertEquals(['hello' => 'world'], $session->getArrayCopy());

        $this->assertEquals('', $this->session->getId());
        $this->assertEquals(['foo' => 'bar'], $this->session->getArrayCopy());
    }

    public function testGetData()
    {
        $this->session['color'] = 'blue';
        $this->assertEquals(['foo' => 'bar', 'color' => 'blue'], $this->session->getData());
    }

    public function testAbort()
    {
        $this->assertFalse($this->session->isAborted());

        $this->session->abort();

        $this->assertTrue($this->session->isAborted());
        $this->assertEquals([], $this->session->getArrayCopy());
    }
    
    public function testDestroy()
    {
        $this->assertFalse($this->session->isDestroyed());
        
        $this->session->destroy();
        
        $this->assertTrue($this->session->isDestroyed());
        $this->assertEquals([], $this->session->getArrayCopy());
    }
    
    public function testUnset()
    {
        $this->session['color'] = 'blue';
        unset($this->session['foo']);
        unset($this->session['nop']);
        
        $this->assertEquals(['color' => 'blue'], $this->session->getArrayCopy());
    }

    public function testFlashCreation()
    {
        $flash = $this->session->flash();

        $this->assertInstanceOf(Flash::class, $flash);
        $this->assertAttributeSame($this->session, 'session', $flash);

        $this->assertEquals(['foo' => 'bar'], $this->session->getArrayCopy());
    }

    public function testFlash()
    {
        $flash = $this->createMock(FlashInterface::class);
        $flashFactory = $this->createMock(FlashFactoryInterface::class);
        $session = $this->session->withFlash($flashFactory);

        $this->assertNotSame($this->session, $session);

        $flashFactory->expects($this->once())->method('create')->with($session, 'foo')
            ->willReturn($flash);

        $this->assertSame($flash, $session->flash('foo'));
    }

    /**
     * @depends testFlash
     */
    public function testCreateWithFlash()
    {
        $flash = $this->createMock(FlashInterface::class);
        $flashFactory = $this->createMock(FlashFactoryInterface::class);

        $base = $this->session->withFlash($flashFactory);
        $session = $base->create('12345', ['hello' => 'world']);

        $flashFactory->expects($this->once())->method('create')->with($session, 'flash')->willReturn($flash);

        $this->assertSame($flash, $session->flash());
    }

    public function testCreateWithFlashNop()
    {
        $flashFactory = $this->createMock(FlashFactoryInterface::class);

        $session = $this->session->withFlash($flashFactory);
        $newSession = $session->withFlash($flashFactory);

        $this->assertSame($session, $newSession);
    }
}
