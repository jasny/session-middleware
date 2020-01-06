<?php

namespace Jasny\Session\Tests;

use Jasny\PHPUnit\CallbackMockTrait;
use Jasny\PHPUnit\ExpectWarningTrait;
use Jasny\Session\SessionInterface;
use Jasny\Session\SessionMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Jasny\Session\SessionMiddleware
 */
class SessionMiddlewareTest extends TestCase
{
    use CallbackMockTrait;
    use ExpectWarningTrait;

    /** @var SessionInterface&MockObject  */
    protected $session;

    protected SessionMiddleware $middleware;

    public function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->middleware = new SessionMiddleware($this->session);
    }

    public function testPsr15WithoutSessionAttribute()
    {
        $requestWithSession = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with('session')
            ->willReturn(null);
        $request->expects($this->once())->method('withAttribute')
            ->with('session', $this->identicalTo($this->session))
            ->willReturn($requestWithSession);

        $this->session->expects($this->once())->method('start');
        $this->session->expects($this->once())->method('stop');

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')
            ->with($this->identicalTo($requestWithSession))
            ->willReturn($response);

        $ret = $this->middleware->process($request, $handler);

        $this->assertSame($response, $ret);
    }

    public function testPsr15WithSessionAttribute()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with('session')
            ->willReturn($this->session);
        $request->expects($this->never())->method('withAttribute');

        $this->session->expects($this->once())->method('start');
        $this->session->expects($this->once())->method('stop');

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $ret = $this->middleware->process($request, $handler);

        $this->assertSame($response, $ret);
    }

    public function testWithInvalidSessionAttribute()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with('session')
            ->willReturn([]);
        $request->expects($this->never())->method('withAttribute');

        $this->session->expects($this->never())->method('start');
        $this->session->expects($this->never())->method('stop');

        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $this->expectWarningMessage("'session' attribute of server request isn't a session object");

        $ret = $this->middleware->process($request, $handler);

        $this->assertSame($response, $ret);
    }


    public function testDoublePassWithoutSessionAttribute()
    {
        $requestWithSession = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with('session')
            ->willReturn(null);
        $request->expects($this->once())->method('withAttribute')
            ->with('session', $this->identicalTo($this->session))
            ->willReturn($requestWithSession);

        $this->session->expects($this->once())->method('start');
        $this->session->expects($this->once())->method('stop');

        $baseResponse = $this->createMock(ResponseInterface::class);

        $next = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($requestWithSession), $this->identicalTo($baseResponse)],
            $response,
        );

        $doublePass = $this->middleware->asDoublePass();
        $this->assertIsCallable($doublePass);

        $ret = $doublePass($request, $baseResponse, $next);

        $this->assertSame($response, $ret);
    }

    public function testDoublePassWithSessionAttribute()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with('session')
            ->willReturn($this->session);
        $request->expects($this->never())->method('withAttribute');

        $response = $this->createMock(ResponseInterface::class);

        $this->session->expects($this->once())->method('start');
        $this->session->expects($this->once())->method('stop');

        $baseResponse = $this->createMock(ResponseInterface::class);

        $next = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($request), $this->identicalTo($baseResponse)],
            $response,
        );

        $doublePass = $this->middleware->asDoublePass();
        $this->assertIsCallable($doublePass);

        $ret = $doublePass($request, $baseResponse, $next);

        $this->assertSame($response, $ret);
    }
}
