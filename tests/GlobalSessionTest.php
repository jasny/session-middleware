<?php

declare(strict_types=1);

namespace Jasny\Session\Tests;

use Jasny\Session\GlobalSession;
use Jasny\Session\NoSessionException;

/**
 * @covers \Jasny\Session\GlobalSession
 */
class GlobalSessionTest extends AbstractSessionTest
{
    public function setUp(): void
    {
        $this->createTestSession();

        $this->session = $this->getMockBuilder(GlobalSession::class)
            ->onlyMethods(['removeCookie', 'regenerateId'])
            ->getMock();

        session_start();
    }

    protected function createTestSession()
    {
        session_id('test');
        session_start();

        $_SESSION = ['foo' => 'bar'];
        session_write_close();
    }

    public function tearDown(): void
    {
        if (session_status() === \PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    protected function assertSessionData(array $expected)
    {
        $this->assertEquals($expected, $_SESSION);
    }


    public function testArrayAccess()
    {
        $this->session['one'] = 1;
        $this->session['two'] = 2;

        $this->assertEquals(['foo' => 'bar', 'one' => 1, 'two' => 2], $_SESSION);
        $this->assertEquals(1, $this->session['one']);

        $this->assertTrue(isset($this->session['one']));
        $this->assertFalse(isset($this->session['zero']));

        unset($this->session['two']);
        $this->assertEquals(['foo' => 'bar', 'one' => 1], $_SESSION);

        unset($this->session['zero']); // No notice
    }

    public function testAssertStarted()
    {
        $this->session->stop();

        $this->expectException(NoSessionException::class);
        $this->expectExceptionMessage("Session not started");

        $this->session['one'] = 1;
    }


    public function testKill()
    {
        $this->session->expects($this->once())->method('removeCookie')->with('PHPSESSID');

        parent::testKill();

        $this->assertNotEquals('test', session_id());

        // Check if the old session is cleared
        session_write_close();
        session_id('test');
        session_start();

        $this->assertEquals([], $_SESSION);
    }

    /**
     * Unable to test `session_regenerate_id` as it always fails because of headers sent.
     */
    public function testRotate()
    {
        $this->session->expects($this->once())->method('regenerateId');

        parent::testRotate();
    }
}
