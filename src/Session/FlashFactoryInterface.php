<?php

declare(strict_types=1);

namespace Jasny\Session;

use Jasny\SessionInterface;
use Jasny\Session\FlashInterface;

/**
 * Interface for flash messages
 */
interface FlashFactoryInterface
{
    /**
     * Create a flash object.
     * If arguments are passed, set the flash message.
     *
     * @param SessionInterface $session
     * @param string|null      $key
     * @return FlashInterface
     */
    public function create(SessionInterface $session, ?string $key = null): FlashInterface;
}
