<?php

declare(strict_types=1);

namespace Jasny\Session\Tests\Flash;

use Jasny\Session\Flash\FlashBag;
use Jasny\Session\Flash\FlashTrait;
use Jasny\Session\MockSession;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Session\Flash\FlashTrait
 */
class FlashTraitTest extends TestCase
{

    public function testFlashes()
    {
        $bag = $this->createMock(FlashBag::class);
        $session = new MockSession([], $bag);

        $sessionBag = $this->createMock(FlashBag::class);
        $bag->expects($this->once())->method('withSession')
            ->with($this->identicalTo($session))
            ->willReturn($sessionBag);

        $this->assertSame($sessionBag, $session->flashes());

        $sessionBag->expects($this->once())->method('withSession')
            ->with($this->identicalTo($session))
            ->willReturnSelf();

        $this->assertSame($sessionBag, $session->flashes());
    }

    public function testFlash()
    {
        $bag = $this->createMock(FlashBag::class);
        $session = new MockSession([], $bag);

        $bag->expects($this->any())->method('withSession')
            ->with($this->identicalTo($session))
            ->willReturnSelf();

        $bag->expects($this->once())->method('add')
            ->with('notice', 'Foo bar', 'text/plain+abc')
            ->willReturnSelf();

        $session->flash('notice', 'Foo bar', 'text/plain+abc');
    }
}
