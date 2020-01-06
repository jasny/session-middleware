<?php

declare(strict_types=1);

namespace Jasny\Session\Flash;

use Jasny\Session\SessionInterface;

/**
 * Trait for flash messages.
 */
trait FlashTrait
{
    protected FlashBag $flashBag;

    /**
     * Add a flash message.
     *
     * @param string $type         flash type, eg. 'error', 'notice' or 'success'
     * @param string $message      flash message
     * @param string $contentType  mime, eg 'text/plain' or 'text/html'
     */
    public function flash(string $type, string $message, string $contentType = 'text/plain'): void
    {
        $this->flashes()->add($type, $message, $contentType);
    }

    /**
     * Get the bag with flash messages.
     */
    public function flashes(): FlashBag
    {
        /** @var SessionInterface $session */
        $session = $this;
        $this->flashBag = $this->flashBag->withSession($session);

        return $this->flashBag;
    }
}
