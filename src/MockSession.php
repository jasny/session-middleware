<?php

declare(strict_types=1);

namespace Jasny\Session;

use Jasny\Session\Flash\FlashBag;
use Jasny\Session\Flash\FlashTrait;

/**
 * Session that only exists in local memory
 */
class MockSession extends \ArrayObject implements SessionInterface
{
    use FlashTrait;

    protected array $initialData;
    protected int $status = \PHP_SESSION_ACTIVE;

    /**
     * MockSession constructor.
     */
    public function __construct(array $input = [], ?FlashBag $flashes = null)
    {
        $this->initialData = $input;
        $this->flashes = $flashes ?? new FlashBag();

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
}
