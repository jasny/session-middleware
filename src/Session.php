<?php

declare(strict_types=1);

namespace Jasny;

use ArrayObject;
use Jasny\SessionInterface;
use Jasny\SessionFactoryInterface;
use Jasny\Session\Flash;
use Jasny\Session\FlashInterface;
use Jasny\Session\FlashFactoryInterface;

/**
 * Session data as object
 */
class Session extends ArrayObject implements SessionInterface, SessionFactoryInterface
{
    /**
     * @var FlashFactoryInterface
     */
    protected $flashFactory;

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
        $session->flashFactory = $this->flashFactory;

        return $session;
    }

    /**
     * Get session object with new flash interface.
     *
     * @param FlashFactoryInterface $flashFactory
     * @return static
     */
    public function withFlash(FlashFactoryInterface $flashFactory): self
    {
        if ($this->flashFactory === $flashFactory) {
            return $this;
        }

        $session = clone $this;
        $session->flashFactory = $flashFactory;

        return $session;
    }


    /**
     * Get the session id
     * 
     * @return string|null
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
     * 
     * @return void
     */
    public function abort(): void
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
     * 
     * @return void
     */
    public function destroy(): void
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


    /**
     * Method for unset()
     * 
     * @param mixed $index
     * @return void
     */
    public function offsetUnset($index): void
    {
        if ($this->offsetExists($index)) {
            parent::offsetUnset($index);
        }
    }
}
