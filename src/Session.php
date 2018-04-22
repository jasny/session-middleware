<?php

declare(strict_types=1);

namespace Jasny;

use ArrayObject;

/**
 * Session data as object
 */
class Session extends ArrayObject implements SessionInterface
{
    /**
     * @var string
     */
    protected $id = '';
    
    /**
     * @var bool
     */
    protected $aborted = false;
    
    /**
     * @var bool
     */
    protected $destroyed = false;
    
    
    /**
     * Factory method
     * 
     * @param string $id
     * @param array  $data
     * @return static
     */
    public function create(string $id, array $data): SessionInterface
    {
        $session = new static($data);
        $session->id = $id;
        
        return $session;
    }
    
    /**
     * Get the session id
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
    
    /**
     * Get session data
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->getArrayCopy();
    }
    
    
    /**
     * Discard session changes
     */
    public function abort()
    {
        $this->aborted = true;
        $this->exchangeArray([]);
    }

    /**
     * Check if the session is aborted
     * 
     * @return bool
     */
    public function isAborted(): bool
    {
        return $this->aborted;
    }
    
    
    /**
     * Destroys all data registered to a session
     */
    public function destroy()
    {
        $this->destroyed = true;
        $this->exchangeArray([]);
    }
    
    /**
     * Check if the session is destroyed
     * 
     * @return bool
     */
    public function isDestroyed(): bool
    {
        return $this->destroyed;
    }
}
