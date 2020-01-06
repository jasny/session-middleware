<?php

namespace Jasny\Session\Tests;

use Jasny\Session\MockSession;

/**
 * @covers \Jasny\Session\MockSession
 *
 * @property MockSession $session
 */
class MockSessionTest extends AbstractSessionTest
{
    public function setUp(): void
    {
        $this->session = new MockSession(['foo' => 'bar']);
    }

    protected function assertSessionData(array $expected)
    {
        $this->assertEquals($expected, $this->session->getArrayCopy());
    }
}
