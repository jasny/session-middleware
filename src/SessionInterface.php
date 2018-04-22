<?php

declare(strict_types=1);

namespace Jasny;

/**
 * A session object
 */
interface SessionInterface extends \ArrayAccess
{
    /**
     * Get the session id
     * 
     * @return string
     */
    public function getId(): string;
    
    /**
     * Get session data
     * 
     * @return array
     */
    public function getData(): array;
    
    /**
     * Discard session changes
     */
    public function abort();

    /**
     * Check if the session is aborted
     * 
     * @return bool
     */
    public function isAborted(): bool;
    
    
    /**
     * Destroys all data registered to a session
     */
    public function destroy();
    
    /**
     * Check if the session is destroyed
     * 
     * @return bool
     */
    public function isDestroyed(): bool;
}
