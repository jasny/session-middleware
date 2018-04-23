<?php

namespace Jasny\ArrayObject;

use PHPUnit\Framework\TestCase;
use Jasny\Session;
use Jasny\Session\Flash;
use ArrayObject;

/**
 * @covers Jasny\Session\Flash
 * 
 * @internal It's difficult to mock an ArrayObject; not mocking ArrayAccess, but using real ArrayObject
 */
class FlashTest extends TestCase
{
    public function testGetEmpty()
    {
        $session = new ArrayObject();
        
        $flash = new Flash('flash', $session);
        
        $this->assertFalse($flash->isIssued(), "Flash should not be issued");
        $this->assertEmpty($flash->get(), "Flash should be empty");
        $this->assertEmpty($flash->getType(), "Flash type should not be set");
        $this->assertEquals('', $flash->getMessage(), "Message should be empty");
        $this->assertEquals('text/plain', $flash->getContentType());

        $this->assertArrayNotHasKey('flash', $session->getArrayCopy());
    }
    
    public function testGetWithExistingFlash()
    {
        $session = new ArrayObject([
            'flash' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
            ]
        ]);
        
        $flash = new Flash('flash',$session);

        $expected = ['type' => 'info', 'message' => 'foobar', 'contentType' => 'text/plain'];

        $this->assertTrue($flash->isIssued());
        $this->assertEquals((object)$expected, $flash->get());
        $this->assertEquals('info', $flash->getType());
        $this->assertEquals('foobar', $flash->getMessage());
        $this->assertEquals('text/plain', $flash->getContentType());

        $this->assertArrayNotHasKey('flash', $session->getArrayCopy(), "Calling get will clear the session data");
    }

    public function testExistingFlashWithoutGet()
    {
        $session = new ArrayObject([
            'flash' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
            ]
        ]);
        
        $flash = new Flash('flash',$session);
        
        $this->assertTrue($flash->isIssued());

        $expected = ['type' => 'info', 'message' => 'foobar', 'contentType' => 'text/plain'];
        $this->assertEquals(['flash' => $expected], $session->getArrayCopy());
    }
    
    public function testSet()
    {
        $session = new ArrayObject();
        $flash = new Flash('flash', $session);
        
        $flash->set('notice', 'zoo');

        $expected = ['type' => 'notice', 'message' => 'zoo', 'contentType' => 'text/plain'];
        $this->assertEquals(['flash' => $expected], $session->getArrayCopy());
        $this->assertEquals((object)$expected, $flash->get());
    }

    public function testSetContentType()
    {
        $session = new ArrayObject();
        $flash = new Flash('flash', $session);

        $flash->set('notice', '<strong>zoo</strong>', 'text/html');

        $expected = ['type' => 'notice', 'message' => '<strong>zoo</strong>', 'contentType' => 'text/html'];
        $this->assertEquals(['flash' => $expected], $session->getArrayCopy());
        $this->assertEquals((object)$expected, $flash->get());
    }

    public function testClear()
    {
        $session = new ArrayObject([
            'flash' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
           ]
        ]);
        
        $flash = new Flash('flash', $session);
        
        $flash->clear();
        
        $this->assertEmpty($flash->get());
        $this->assertEmpty($session->getArrayCopy());
    }
    
    public function testClearAfterGet()
    {
        $session = new ArrayObject([
            'flash' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
            ]
        ]);
        
        $flash = new Flash('flash', $session);

        $expected = ['type' => 'info', 'message' => 'foobar', 'contentType' => 'text/plain'];
        $this->assertEquals((object)$expected, $flash->get());
        
        $flash->clear();
        
        $this->assertEmpty($flash->get());
        $this->assertEmpty($session->getArrayCopy());
    }
    
    public function testClearAfterSet()
    {
        $session = new ArrayObject();
        $flash = new Flash('flash', $session);
        
        $flash->set('notice', 'zoo');

        $expected = ['type' => 'notice', 'message' => 'zoo', 'contentType' => 'text/plain'];
        $this->assertEquals(['flash' => $expected], $session->getArrayCopy());
        
        $flash->clear();
        
        $this->assertFalse($flash->isIssued());
        $this->assertEmpty($session->getArrayCopy());
    }
    
    public function testReissueAfterGet()
    {
        $session = new ArrayObject([
            'flash' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
            ]
        ]);
        
        $flash = new Flash('flash', $session);

        $expected = ['type' => 'info', 'message' => 'foobar', 'contentType' => 'text/plain'];
        $this->assertEquals((object)$expected, $flash->get());
        $this->assertEmpty($session);
        
        $flash->reissue();
        
        $this->assertEquals(['flash' => $expected], $session->getArrayCopy());
    }
    
    public function testReissueBeforeGet()
    {
        $session = new ArrayObject([
            'flash' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
            ]
        ]);
        
        $flash = new Flash('flash', $session);
        
        $flash->reissue();

        $expected = ['type' => 'info', 'message' => 'foobar', 'contentType' => 'text/plain'];
        $this->assertEquals((object)$expected, $flash->get());
        
        $this->assertEquals(['flash' => $expected], $session->getArrayCopy());
    }
    
    public function testAlternativeName()
    {
        $session = new ArrayObject([
            'foo' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
            ]
        ]);
        
        $flash = new Flash('foo', $session);

        $expectedBeforeSet = ['type' => 'info', 'message' => 'foobar', 'contentType' => 'text/plain'];
        $this->assertEquals((object)$expectedBeforeSet, $flash->get());
        
        $flash->set('success', 'nailed it');

        $expectedAfterSet = ['type' => 'success', 'message' => 'nailed it', 'contentType' => 'text/plain'];
        $this->assertEquals(['foo' => $expectedAfterSet], $session->getArrayCopy());
    }
    
    public function testOtherArrayObjectData()
    {
        $session = new ArrayObject([
            'flash' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
            ],
            'uid' => 10
        ]);
        
        $flash = new Flash('flash', $session);

        $flash->set('success', 'nailed it');

        $expected = ['type' => 'success', 'message' => 'nailed it', 'contentType' => 'text/plain'];
        $this->assertEquals(['flash' => $expected, 'uid' => 10], $session->getArrayCopy());
    }

    /**
     * @expectedException PHPUnit\Framework\Error\Warning
     * @expectedExceptionMessage Invalid session data for flash message
     */
    public function testInvalidFlashArrayObjectData()
    {
        $session = new ArrayObject([
            'flash' => 'not an object'
        ]);
        
        $flash = new Flash('flash', $session);
        $flash->get();
    }

    public function testInvalidFlashArrayObjectDataRet()
    {
        $session = new ArrayObject([
            'flash' => 'not an object'
        ]);

        $flash = new Flash('flash', $session);
        $this->assertNull(@$flash->get());
    }

    /**
     * @expectedException PHPUnit\Framework\Error\Warning
     * @expectedExceptionMessage Invalid session data for flash message
     */
    public function testInvalidFlashArrayObjectDataStruct()
    {
        $session = new ArrayObject([
            'flash' => ['hello' => 'world']
        ]);
        
        $flash = new Flash('flash', $session);
        $flash->get();
    }

    public function testCreate()
    {
        $session = new Session([
            'flash' => [
                'type' => 'info',
                'message' => 'foobar',
                'contentType' => 'text/plain'
            ]
        ]);

        $factory = new Flash();
        $flash = $factory->create($session, 'flash');

        $this->assertNotSame($factory, $flash);

        $expected = ['type' => 'info', 'message' => 'foobar', 'contentType' => 'text/plain'];
        $this->assertEquals((object)$expected, $flash->get());
    }
}
