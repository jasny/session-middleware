<?php

declare(strict_types=1);

namespace Jasny\Session;

use Jasny\Session\Flash\FlashBag;
use Jasny\Session\Flash\FlashTrait;

/**
 * Session that only exists in local memory.
 *
 * @extends \ArrayObject<string,mixed>
 */
class MockSession extends \ArrayObject implements SessionInterface
{
    use FlashTrait;

    /** @var array<string,mixed> */
    protected array $initialData;
    protected int $status = \PHP_SESSION_ACTIVE;

    /**
     * MockSession constructor.
     *
     * @param array<string,mixed> $input
     * @param FlashBag|null       $flashBag
     */
    public function __construct(array $input = [], ?FlashBag $flashBag = null)
    {
        $this->initialData = $input;
        $this->flashBag = $flashBag ?? new FlashBag();

        parent::__construct($input);
    }

    /**
     * @inheritDoc
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        $this->status = \PHP_SESSION_ACTIVE;
    }

    /**
     * @inheritDoc
     */
    public function stop(): void
    {
        $this->initialData = $this->getArrayCopy();
        $this->status = \PHP_SESSION_NONE;
    }

    /**
     * @inheritDoc
     */
    public function abort(): void
    {
        $this->exchangeArray($this->initialData);
        $this->status = \PHP_SESSION_NONE;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->exchangeArray([]);
    }


    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        if (parent::offsetExists($offset)) {
            parent::offsetUnset($offset);
        }
    }
}
