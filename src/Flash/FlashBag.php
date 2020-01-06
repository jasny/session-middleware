<?php

declare(strict_types=1);

namespace Jasny\Session\Flash;

/**
 * Flash messages are stored in the session and cleared after they're used.
 *
 * @implements \IteratorAggregate<int,Flash>
 */
class FlashBag implements \IteratorAggregate
{
    /** @var \ArrayAccess<string,mixed> */
    protected \ArrayAccess $session;
    protected string $key;

    /** @var Flash[] */
    protected array $entries = [];

    /**
     * Class constructor.
     */
    public function __construct(string $key = 'flash')
    {
        $this->key = $key;
    }


    /**
     * Get iterator for entries.
     *
     * @return \ArrayIterator<int,Flash>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->entries);
    }

    /**
     * Get all entries as array.
     *
     * @return Flash[]
     */
    public function getArrayCopy(): array
    {
        return $this->entries;
    }


    /**
     * Get copy with session object.
     *
     * @param \ArrayAccess<string,mixed> $session
     * @return static
     */
    public function withSession(\ArrayAccess $session): self
    {
        if (isset($this->session) && $this->session === $session) {
            return $this;
        }

        $copy = clone $this;
        $copy->session = $session;

        $copy->initFromSession();

        return $copy;
    }

    /**
     *  Initialize the entries from the session.
     */
    protected function initFromSession(): void
    {
        if (!isset($this->session[$this->key])) {
            return;
        }

        $entries = $this->session[$this->key];
        unset($this->session[$this->key]);

        if (!is_array($entries)) {
            trigger_error('Invalid flash session data', E_USER_WARNING);
            return;
        }

        $this->initEntries($entries);
    }

    /**
     * Initialize the entries.
     *
     * @param array<mixed> $entries
     */
    protected function initEntries(array $entries): void
    {
        $invalid = 0;

        foreach ($entries as $entry) {
            if (!is_array($entry) || !isset($entry['message'])) {
                $invalid++;
                continue;
            }

            $this->entries[] = new Flash(
                $entry['type'] ?? '',
                $entry['message'],
                $entry['contentType'] ?? 'text/plain'
            );
        }

        if ($invalid > 0) {
            trigger_error(
                $invalid === 1 ? "Ignored invalid flash message" : "Ignoring $invalid invalid flash messages",
                E_USER_NOTICE,
            );
        }
    }


    /**
     * Add a flash message.
     *
     * @param string $type         flash type, eg. 'error', 'notice' or 'success'
     * @param string $message      flash message
     * @param string $contentType  mime, eg 'text/plain' or 'text/html'
     * @return $this
     */
    public function add(string $type, string $message, string $contentType = 'text/plain'): self
    {
        $this->session[$this->key][] = ['type' => $type, 'message' => $message, 'contentType' => $contentType];

        return $this;
    }

    /**
     * Reissue the flash messages.
     * Get it now and it will remain in the session for next request.
     */
    public function reissue(): self
    {
        $this->session[$this->key] = array_merge(
            array_map(fn(Flash $flash) => $flash->toAssoc(), $this->entries),
            $this->session[$this->key] ?? [],
        );

        $this->entries = [];

        return $this;
    }

    /**
     * Clear all flash messages.
     */
    public function clear(): self
    {
        $this->entries = [];

        if (isset($this->session[$this->key])) {
            unset($this->session[$this->key]);
        }

        return $this;
    }
}
