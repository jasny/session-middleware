<?php

namespace Jasny\Session;

use PHPUnit\Framework\TestCase;
use Jasny\Session;

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
}
