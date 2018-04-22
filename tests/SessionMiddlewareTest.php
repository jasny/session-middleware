<?php

namespace Jasny\Session;

use PHPUnit\Framework\TestCase;
use Jasny\SessionMiddleware;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Jasny\SessionInterface;
use Jasny\SessionFactoryInterface;
use SessionHandler;
use SessionHandlerInterface;
use SessionIdInterface;

/**
 * @covers Jasny\SessionMiddleware
 */
class SessionMiddlewareTest extends TestCase
{
    use \Jasny\TestHelper;
    
    public function testCreateSessionNew()
    {
        $handler = $this->createMock(SessionHandlerInterface::class);
        $handler->expects($this->never())->method('read');
        
        $idgen = $this->createMock(SessionIdInterface::class);
        $idgen->expects($this->once())->method('create_sid')->willReturn('00000');
        
        $session = $this->createMock(SessionInterface::class);
        
        $sessionFactory = $this->createMock(SessionFactoryInterface::class);
        $sessionFactory->expects($this->once())->method('create')->with('00000', [])->willReturn($session);
        
        $encode = $this->createCallbackMock($this->never());
        $decode = $this->createCallbackMock($this->never());
        
        $middleware = (new SessionMiddleware())
            ->withSessionParams('ses', [])
            ->withSessionHandler($handler, $idgen)
            ->withSessionFactory($sessionFactory)
            ->withEncoder($encode, $decode);
        
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getCookieParams')->willReturn([]);
        
        $ret = $middleware->createSession($request);
        
        $this->assertSame($session, $ret);
    }
    
    public function testCreateSessionRead()
    {
        $handler = $this->createMock(SessionHandlerInterface::class);
        $handler->expects($this->once())->method('read')->with('12345')->willReturn('foo=bar');
        
        $idgen = $this->createMock(SessionIdInterface::class);
        $idgen->expects($this->never())->method('create_sid');
        
        $session = $this->createMock(SessionInterface::class);
        
        $sessionFactory = $this->createMock(SessionFactoryInterface::class);
        $sessionFactory->expects($this->once())->method('create')->with('12345', ['foo' => 'bar'])
            ->willReturn($session);
                
        $encode = $this->createCallbackMock($this->never());
        $decode = $this->createCallbackMock($this->once(), ['foo=bar'], ['foo' => 'bar']);
        
        $middleware = (new SessionMiddleware())
            ->withSessionParams('ses', [])
            ->withSessionHandler($handler, $idgen)
            ->withSessionFactory($sessionFactory)
            ->withEncoder($encode, $decode);
        
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getCookieParams')->willReturn(['ses' => '12345']);
        
        $ret = $middleware->createSession($request);
        
        $this->assertSame($session, $ret);
    }
    
    public function cookieProvider()
    {
        return [
            [[], 'ses=12345'],
            [['path' => '/users'], 'ses=12345; path=/users'],
            [['domain' => 'example.com'], 'ses=12345; domain=example.com'],
            [['secure' => false], 'ses=12345'],
            [['secure' => true], 'ses=12345; secure'],
            [['httponly' => false], 'ses=12345'],
            [['httponly' => true], 'ses=12345; httponly'],
            [
                ['path' => '/users', 'domain' => 'example.com', 'secure' => true, 'httponly' => true],
                'ses=12345; path=/users; domain=example.com; secure; httponly'
            ],
            [['lifetime' => 0], 'ses=12345'],
            [['lifetime' => 100], $this->callback(function($value) {
                $regex = '/ses=12345; expires=(\w{3}, \d{1,2}-\w{3}-\d{4} \d\d:\d\d:\d\d [\w:]+)/';
                return preg_match($regex, $value, $match) && abs(strtotime($match[1]) - time() - 100) < 5;
            })]
        ];
    }
    
    /**
     * @dataProvider CookieProvider
     */
    public function testStoreSession($cookieParams, $cookie)
    {
        $handler = $this->createMock(SessionHandlerInterface::class);
        $handler->expects($this->once())->method('write')->with('12345', 'foo=bar');
        
        $idgen = $this->createMock(SessionIdInterface::class);
        $sessionFactory = $this->createMock(SessionFactoryInterface::class);
                
        $encode = $this->createCallbackMock($this->once(), [['foo' => 'bar']], 'foo=bar');
        $decode = $this->createCallbackMock($this->never());

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('getId')->willReturn('12345');
        $session->expects($this->once())->method('getData')->willReturn(['foo' => 'bar']);
        $session->expects($this->any())->method('isDestroyed')->willReturn(false);
        $session->expects($this->any())->method('isAborted')->willReturn(false);

        $middleware = (new SessionMiddleware())
            ->withSessionParams('ses', $cookieParams)
            ->withSessionHandler($handler, $idgen)
            ->withSessionFactory($sessionFactory)
            ->withEncoder($encode, $decode);

        $newResponse = $this->createMock(ResponseInterface::class);
        
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('withAddedHeader')->with('Set-Cookie', $cookie)
            ->willReturn($newResponse);
        
        $ret = $middleware->storeSession($session, $response);
        
        $this->assertSame($newResponse, $ret);
    }
    
    public function testStoreSessionDestroyed()
    {
        $handler = $this->createMock(SessionHandlerInterface::class);
        $handler->expects($this->never())->method('write');
        
        $idgen = $this->createMock(SessionIdInterface::class);
        $sessionFactory = $this->createMock(SessionFactoryInterface::class);
                
        $encode = $this->createCallbackMock($this->never());
        $decode = $this->createCallbackMock($this->never());

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('getId')->willReturn('12345');
        $session->expects($this->never())->method('getData');
        $session->expects($this->any())->method('isDestroyed')->willReturn(true);
        $session->expects($this->any())->method('isAborted')->willReturn(false);

        $middleware = (new SessionMiddleware())
            ->withSessionParams('ses', [])
            ->withSessionHandler($handler, $idgen)
            ->withSessionFactory($sessionFactory)
            ->withEncoder($encode, $decode);

        $newResponse = $this->createMock(ResponseInterface::class);
        
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('withAddedHeader')
            ->with('Set-Cookie', 'ses=12345; expires=Thu, 01-Jan-1970 00:00:00 GMT')->willReturn($newResponse);
        
        $ret = $middleware->storeSession($session, $response);
        
        $this->assertSame($newResponse, $ret);
    }
    
    public function testStoreSessionAborted()
    {
        $handler = $this->createMock(SessionHandlerInterface::class);
        $handler->expects($this->never())->method('write');
        
        $idgen = $this->createMock(SessionIdInterface::class);
        $sessionFactory = $this->createMock(SessionFactoryInterface::class);
                
        $encode = $this->createCallbackMock($this->never());
        $decode = $this->createCallbackMock($this->never());

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('getId')->willReturn('12345');
        $session->expects($this->never())->method('getData');
        $session->expects($this->any())->method('isDestroyed')->willReturn(false);
        $session->expects($this->any())->method('isAborted')->willReturn(true);

        $middleware = (new SessionMiddleware())
            ->withSessionParams('ses', [])
            ->withSessionHandler($handler, $idgen)
            ->withSessionFactory($sessionFactory)
            ->withEncoder($encode, $decode);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->never())->method('withAddedHeader');
        
        $ret = $middleware->storeSession($session, $response);
        
        $this->assertSame($response, $ret);
    }
    
    
    public function testProcess()
    {
        $session = $this->createMock(SessionInterface::class);
        
        $sessionRequest = $this->createMock(ServerRequestInterface::class);
        
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')->with('session')->willReturn(null);
        $request->expects($this->once())->method('withAttribute')->with('session', $session)
            ->willReturn($sessionRequest);
        
        $cookieResponse = $this->createMock(ResponseInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($sessionRequest)->willReturn($response);
        
        $middleware = $this->getMockBuilder(SessionMiddleware::class)->disableOriginalConstructor()
            ->setMethods(['createSession', 'storeSession'])->getMock();
        $middleware->expects($this->once())->method('createSession')->with($request)->willReturn($session);
        $middleware->expects($this->once())->method('storeSession')->with($session, $response)
            ->willReturn($cookieResponse);
        
        $ret = $middleware->process($request, $handler);
        
        $this->assertSame($cookieResponse, $ret);
    }
    
    public function testProcessMockSession()
    {
        $session = $this->createMock(SessionInterface::class);
        
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')->with('session')->willReturn($session);
        $request->expects($this->never())->method('withAttribute');
        
        $response = $this->createMock(ResponseInterface::class);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);
        
        $middleware = $this->getMockBuilder(SessionMiddleware::class)->disableOriginalConstructor()
            ->setMethods(['createSession', 'storeSession'])->getMock();
        $middleware->expects($this->never())->method('createSession');
        $middleware->expects($this->never())->method('storeSession');
        
        $ret = $middleware->process($request, $handler);
        
        $this->assertSame($response, $ret);
    }
    
    public function testInvoke()
    {
        $session = $this->createMock(SessionInterface::class);
        
        $sessionRequest = $this->createMock(ServerRequestInterface::class);
        
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')->with('session')->willReturn(null);
        $request->expects($this->once())->method('withAttribute')->with('session', $session)
            ->willReturn($sessionRequest);
        
        $cookieResponse = $this->createMock(ResponseInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $baseResponse = $this->createMock(ResponseInterface::class);

        $next = $this->createCallbackMock($this->once(), [$request, $baseResponse], $response);
        
        $middleware = $this->getMockBuilder(SessionMiddleware::class)->disableOriginalConstructor()
            ->setMethods(['createSession', 'storeSession'])->getMock();
        $middleware->expects($this->once())->method('createSession')->with($request)->willReturn($session);
        $middleware->expects($this->once())->method('storeSession')->with($session, $response)
            ->willReturn($cookieResponse);
        
        $ret = $middleware->__invoke($request, $baseResponse, $next);
        
        $this->assertSame($cookieResponse, $ret);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testWithSessionHandlerNoSessionIdGenerator()
    {
        $handler = $this->createMock(SessionHandlerInterface::class);
        
        (new SessionMiddleware())->withSessionHandler($handler);
    }

    public function testWithSessionHandlerIsSessionIdGenerator()
    {
        $handler = $this->createMock(SessionHandler::class);
        
        $middleware = (new SessionMiddleware())->withSessionHandler($handler);
        
        $this->assertAttributeSame($handler, 'idGenerator', $middleware);
    }
}
