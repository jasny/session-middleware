<?php

namespace Jasny\Session\Tests;

use Jasny\Session\SessionInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractSessionTest extends TestCase
{
    protected SessionInterface $session;

    abstract protected function assertSessionData(array $expected);

    public function testStartStop()
    {
        $this->assertEquals(PHP_SESSION_ACTIVE, $this->session->status());

        $this->session->stop();
        $this->assertEquals(PHP_SESSION_NONE, $this->session->status());

        $this->session->start();
        $this->assertEquals(PHP_SESSION_ACTIVE, $this->session->status());
    }

    public function testAbort()
    {
        $this->session['zoo'] = 10;
        $this->assertSessionData(['foo' => 'bar', 'zoo' => 10]);

        $this->session->abort();
        $this->assertEquals(PHP_SESSION_NONE, $this->session->status());

        $this->session->start();
        $this->assertEquals(PHP_SESSION_ACTIVE, $this->session->status());
        $this->assertSessionData(['foo' => 'bar']);
    }

    public function testAbortAfterStop()
    {
        $this->session['zoo'] = 10;
        $this->session->stop();

        $this->session->start();
        $this->session->abort();

        $this->session->start();
        $this->assertSessionData(['foo' => 'bar', 'zoo' => 10]);
    }

    public function testClear()
    {
        $this->assertSessionData(['foo' => 'bar']);

        $this->session->clear();
        $this->assertSessionData([]);
        $this->assertEquals(PHP_SESSION_ACTIVE, $this->session->status());
    }

    public function testKill()
    {
        $this->assertSessionData(['foo' => 'bar']);

        $this->session->kill();
        $this->assertSessionData([]);
        $this->assertEquals(PHP_SESSION_NONE, $this->session->status());
    }

    public function testRotate()
    {
        $this->assertSessionData(['foo' => 'bar']);

        $this->session->rotate();
        $this->assertSessionData([]);
        $this->assertEquals(PHP_SESSION_ACTIVE, $this->session->status());
    }
}
