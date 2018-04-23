<?php

declare(strict_types=1);

namespace Jasny\Session;

use stdClass;
use ArrayAccess;
use ArrayObject;
use Jasny\SessionInterface;
use Jasny\Session\FlashInterface;
use Jasny\Session\FlashFactoryInterface;

/**
 * Class for flash messages
 */
class Flash implements FlashInterface, FlashFactoryInterface
{
    /**
     * @var SessionInterface|ArrayAccess
     */
    protected $session = [];

    /**
     * @var string
     */
    protected $key;
    
    /**
     * @var stdClass|null
     */
    protected $data;
    
    
    /**
     * Class constructor
     *
     * @param string      $key
     * @param ArrayAccess $session
     */
    public function __construct(string $key = 'flash', ArrayAccess $session = null)
    {
        $this->key = $key;
        $this->session = $session ?? new ArrayObject();
    }

    /**
     * Create a flash object with a different session object
     *
     * @param SessionInterface $session
     * @param string|null      $key
     * @return static
     */
    public function create(SessionInterface $session, ?string $key = null): FlashInterface
    {
        return new static($key ?? $this->key, $session);
    }

    
    /**
     * Check if the flash is set.
     * 
     * @return bool
     */
    public function isIssued(): bool
    {
        return isset($this->session[$this->key]);
    }
    
    /**
     * Set the flash.
     *
     * @param string $type         flash type, eg. 'error', 'notice' or 'success'
     * @param string $message      flash message
     * @param string $contentType  flash message Content-Type
     */
    public function set(string $type, string $message, string $contentType = 'text/plain'): void
    {
        $this->session[$this->key] = compact('type', 'message', 'contentType');
    }
    
    /**
     * Get the data from the session
     * 
     * @return void
     */
    protected function initFromSession(): void
    {
        $data = $this->session[$this->key];

        if (!(is_array($data) && isset($data['message'])) && !($data instanceof \stdClass && isset($data->message))) {
            trigger_error("Invalid session data for flash message", E_USER_WARNING);
            return;
        }

        $this->data = (object)$data;
    }
    
    /**
     * Get the flash.
     * 
     * @return stdClass|null
     */
    public function get(): ?stdClass
    {
        if (!isset($this->data) && isset($this->session[$this->key])) {
            $this->initFromSession();
            unset($this->session[$this->key]);
        }
        
        return $this->data;
    }
    
    /**
     * Reissue the flash.
     * Get it now and it will remain in the session for next request.
     * 
     * @return void
     */
    public function reissue(): void
    {
        if (!isset($this->data) && isset($this->session[$this->key])) {
            $this->initFromSession();
        } elseif (isset($this->data)) {
            $this->session[$this->key] = (array)$this->data;
        }
    }
    
    /**
     * Clear the flash.
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->data = null;

        if (isset($this->session[$this->key])) {
            unset($this->session[$this->key]);
        }
    }
    
    /**
     * Get the flash type.
     * 
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->get()->type ?? null;
    }
    
    /**
     * Get the flash message
     * 
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->get()->message ?? null;
    }

    /**
     * Get the Content-Type of the flash message.
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->get()->contentType ?? 'text/plain';
    }
}
