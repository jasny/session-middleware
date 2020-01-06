<?php

declare(strict_types=1);

namespace Jasny\Session\Tests;

use Jasny\Session\GlobalSession;

/**
 * @covers \Jasny\Session\GlobalSession
 */
class GlobalSessionTest extends AbstractSessionTest
{
    public function setUp(): void
    {
        $this->createTestSession();

        $this->session = new GlobalSession();
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

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Session not started");

        $this->session['one'] = 1;
    }
}
