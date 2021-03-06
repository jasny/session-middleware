<?php

declare(strict_types=1);

namespace Jasny\Session;

use Jasny\Session\Flash\FlashBag;
use Jasny\Session\Flash\FlashTrait;

/**
 * Wrapper round $_SESSION.
 */
class GlobalSession implements SessionInterface
{
    use FlashTrait;

    /** @var array<string,mixed> */
    protected array $options;

    /**
     * Session constructor.
     *
     * @param array<string,mixed> $options   Passed to session_start()
     * @param FlashBag|null       $flashBag
     */
    public function __construct(array $options = [], ?FlashBag $flashBag = null)
    {
        $this->options = $options;
        $this->flashBag = $flashBag ?? new FlashBag();
    }

    /**
     * Start the session.
     * @see session_start()
     */
    public function start(): void
    {
        session_start($this->options);
    }

    /**
     * Write session data and end session.
     * @see session_write_close()
     */
    public function stop(): void
    {
        session_write_close();
    }

    /**
     * Discard session array changes and finish session.
     * @see session_abort()
     *
     * Only a shallow clone is done to save the original data. If the session data contains objects, make sure that
     *   `__clone()` is overwritten, so it does a deep clone.
     */
    public function abort(): void
    {
        session_abort();
    }


    /**
     * Get the sessions status.
     * @see session_status()
     */
    public function status(): int
    {
        return session_status();
    }


    /**
     * Clear all data from the session.
     */
    public function clear(): void
    {
        $this->assertStarted();

        $_SESSION = [];
    }

    /**
     * @inheritDoc
     */
    public function kill(): void
    {
        $this->assertStarted();

        $this->removeCookie(session_name());
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Remove the session cookie.
     * @codeCoverageIgnore
     */
    protected function removeCookie(string $name): void
    {
        if (!(bool)ini_get("session.use_cookies")) {
            return;
        }

        $options = ['expires' => time() - 42000] + session_get_cookie_params();
        unset($options['lifetime']);

        setcookie($name, '', $options);
    }

    /**
     * @inheritDoc
     */
    public function rotate(?callable $copy = null): void
    {
        $this->assertStarted();

        $data = isset($copy) ? $copy($_SESSION) : [];

        $_SESSION = [];
        $this->regenerateId(true);

        $_SESSION = $data;
    }

    /**
     * Wrapper around `session_regenerate_id()`
     * @codeCoverageIgnore
     */
    protected function regenerateId(bool $delete): void
    {
        session_regenerate_id($delete);
    }


    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        $this->assertStarted();

        return array_key_exists($offset, $_SESSION);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $this->assertStarted();

        return $_SESSION[$offset];
    }

    /**
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->assertStarted();

        $_SESSION[$offset] = $value;
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->assertStarted();

        unset($_SESSION[$offset]);
    }


    /**
     * Assert that there is an active session.
     *
     * @throws NoSessionException
     */
    protected function assertStarted(): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            throw new NoSessionException("Session not started");
        }
    }
}
