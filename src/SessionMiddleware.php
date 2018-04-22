<?php

declare(strict_types=1);

namespace Jasny;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Jasny\Session;
use Jasny\SessionInterface;
use SessionHandler;
use SessionHandlerInterface;
use SessionIdInterface;
use BadMethodCallException;

/**
 * Load and store sessions using a session handler
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * Session name
     * @var string
     */
    protected $name;
    
    /**
     * Session cookie parameters
     * @var string
     */
    protected $cookieParams;
    
    /**
     * Session handler
     * @var SessionHandlerInterface
     */
    protected $handler;
    
    /**
     * Session id generator
     * @var SessionIdInterface 
     */
    protected $idGenerator;
    
    /**
     * Base session (will be cloned)
     * @var SessionInterface
     */
    protected $baseSession;
    
    /**
     * Method to encode the session
     * @var callable
     */
    protected $encode;
    
    /**
     * Method to decode the session
     * @var callable
     */
    protected $decode;
    
    
    /**
     * Class constructor
     * 
     * @param string                  $name
     * @param SessionHandlerInterface $handler
     * @param SessionIdInterface      $idGenerator
     * @param SessionInterface        $baseSession
     * @param array                   $cookieParams
     * @param callable                $encode
     * @param callable                $decode
     */
    public function __construct(
        string $name = null,
        SessionHandlerInterface $handler = null,
        SessionIdInterface $idGenerator = null,
        SessionInterface $baseSession = null,
        array $cookieParams = null,
        callable $encode = null,
        callable $decode = null
    )
    {
        if (isset($handler) && !isset($idGenerator) && !$handler instanceof SessionIdInterface) {
            throw new BadMethodCallException("Handler can't generate a session id and no generator specified");
        }
        
        $this->name = $name ?? session_name();
        $this->handler = $handler ?? new SessionHandler();
        $this->idGenerator = $idGenerator ?? $handler;
        $this->baseSession = $baseSession ?? new Session();
        $this->cookieParams = $cookieParams ?? session_get_cookie_params();
        
        $this->encode = $encode ?? new Session\Encoder();
        $this->decode = $decode ?? new Session\Decoder();
    }

    
    /**
     * Create or load a session.
     * 
     * @param ServerRequestInterface $request
     * @return SessionInterface
     */
    public function createSession(ServerRequestInterface $request): SessionInterface
    {
        $cookies = $request->getCookieParams();
        
        if (isset($cookies[$this->name])) {
            $sessionId = $cookies[$this->name];
            $encodedData = $this->handler->read($sessionId);
            $data = !empty($encodedData) ? call_user_func($this->decode, $encodedData) : [];
        } else {
            $sessionId = $this->idGenerator->create_sid();
            $data = [];
        }
        
        return $this->baseSession->create($sessionId, $data);
    }
    
    /**
     * Get the header to set the session cookie.
     * 
     * @param string $sessionId
     * @param int    $expires
     * @return string
     */
    protected function getCookieHeader(string $sessionId, int $expires = null): string
    {
        if (!isset($expires) && !empty($this->cookieParams['lifetime'])) {
            $expires = time() + $this->cookieParams['lifetime'];
        }
        
        return sprintf('%s=%s', $this->name, urlencode($sessionId))
            . (isset($expires) ? sprintf('; expires=%s', gmdate('D, d-M-Y H:i:s T', $expires)) : '')
            . (!empty($this->cookieParams['path']) ? sprintf('; path=%s', $this->cookieParams['path']) : '')
            . (!empty($this->cookieParams['domain']) ? sprintf('; domain=%s', $this->cookieParams['domain']) : '')
            . (!empty($this->cookieParams['secure']) ? '; secure' : '')
            . (!empty($this->cookieParams['httponly']) ? '; httponly' : '');
    }
    
    /**
     * Store a session
     * 
     * @param string            $sessionId
     * @param SessionInterface  $session
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function storeSession(SessionInterface $session, ResponseInterface $response)
    {
        $sessionId = $session->getId();
        
        if ($session->isDestroyed()) {
            $this->handler->destroy($sessionId);
            
            return $response->withAddedHeader('Set-Cookie', $this->getCookieHeader($sessionId, 0));
        }
        
        if ($session->isAborted()) {
            return $response;
        }
        
        $encodedData = call_user_func($this->encode, $session->getData());
        $this->handler->write($sessionId, $encodedData);

        return $response->withAddedHeader('Set-Cookie', $this->getCookieHeader($sessionId));
    }
    
    
    /**
     * Run the middleware
     * 
     * @param ServerRequestInterface $request
     * @param callable               $handle
     * @return ResponseInterface
     */
    protected function run(ServerRequestInterface $request, callable $handle): ResponseInterface
    {
        if ($request->getAttribute('session') === null) {
            $session = $this->createSession($request);
            $request = $request->withAttribute('session', $session);
        }
        
        $response = $handle($request);
        
        if (isset($session)) {
            $response = $this->storeSession($session, $response);
        }
        
        return $response;
    }
    
    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->run($request, [$handler, 'handle']);
    }
    
    /**
     * In/out middleware support
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface
    {
        return $this->run($request, function(ServerRequestInterface $request) use ($response, $next) {
            return $next($request, $response);
        });
    }
}
