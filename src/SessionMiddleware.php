<?php

declare(strict_types=1);

namespace Jasny\Session;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Middleware to start a session.
 * The session can be mocked by setting the 'session' attribute of the server request.
 */
class SessionMiddleware implements MiddlewareInterface
{
    protected SessionInterface $defaultSession;

    /**
     * Class constructor.
     */
    public function __construct(?SessionInterface $session = null)
    {
        $this->defaultSession = $session ?? new GlobalSession();
    }

    /**
     * Process an incoming server request (PSR-15).
     *
     * @param ServerRequest  $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(ServerRequest $request, RequestHandler $handler): Response
    {
        return $this->run($request, \Closure::fromCallable([$handler, 'handle']));
    }

    /**
     * Get a callback that can be used as double pass middleware.
     *
     * @return callable(ServerRequest,Response,callable):Response
     */
    public function asDoublePass(): callable
    {
        return function (ServerRequest $request, Response $response, callable $next): Response {
            return $this->run($request, fn($request) => $next($request, $response));
        };
    }

    /**
     * @param ServerRequest $request
     * @param callable      $handle
     * @return Response
     */
    protected function run(ServerRequest $request, callable $handle): Response
    {
        $session = $request->getAttribute('session');

        if ($session !== null && !$session instanceof SessionInterface) {
            trigger_error("'session' attribute of server request isn't a session object", E_USER_WARNING);
            return $handle($request);
        }

        if ($session === null) {
            $session = $this->defaultSession;
            $request = $request->withAttribute('session', $session);
        }

        $session->start();

        try {
            return $handle($request);
        } finally {
            $session->stop();
        }
    }
}
