<?php

declare(strict_types=1);

namespace Jasny\Session;

use stdClass;
use Jasny\SessionInterface;

/**
 * Interface for flash messages
 */
interface FlashInterface
{
    /**
     * Check if the flash is set.
     *
     * @return bool
     */
    public function isIssued(): bool;

    /**
     * Set the flash.
     *
     * @param string $type         flash type, eg. 'error', 'notice' or 'success'
     * @param string $message      flash message
     * @param string $contentType  flash message Content-Type
     */
    public function set(string $type, string $message, string $contentType): void;

    /**
     * Get the flash.
     *
     * @return stdClass|null
     */
    public function get(): ?stdClass;

    /**
     * Reissue the flash.
     * Get it now and it will remain in the session for next request.
     *
     * @return void
     */
    public function reissue(): void;

    /**
     * Clear the flash.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Get the flash type.
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Get the flash message
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Get the Content-Type of the flash message.
     *
     * @return string
     */
    public function getContentType(): string;
}
