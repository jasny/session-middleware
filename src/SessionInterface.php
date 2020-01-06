<?php

declare(strict_types=1);

namespace Jasny\Session;

use Jasny\Session\Flash\FlashBag;

/**
 * Session data as object.
 */
interface SessionInterface extends \ArrayAccess
{
    /**
     * Start the session.
     * @see session_start()
     */
    public function start(): void;

    /**
     * Get the sessions status.
     * @see session_status()
     */
    public function status(): int;

    /**
     * Write session data and end session.
     * @see session_write_close()
     */
    public function stop(): void;

    /**
     * Discard session array changes and finish session.
     * @see session_abort()
     */
    public function abort(): void;


    /**
     * Clear all data from the session.
     */
    public function clear(): void;


    /**
     * Add a flash message.
     *
     * @param string $type         flash type, eg. 'error', 'notice' or 'success'
     * @param string $message      flash message
     * @param string $contentType  mime, eg 'text/plain' or 'text/html'
     */
    public function flash(string $type, string $message, string $contentType = 'text/plain'): void;

    /**
     * Get the service for flash messages.
     */
    public function flashes(): FlashBag;
}
