<?php

namespace Jasny;

/**
 * Interface for object than can create new session objects.
 */
interface SessionFactoryInterface
{
    /**
     * Factory method
     * 
     * @param string $id
     * @param array  $data
     * @return SessionInterface
     */
    public function create(string $id, array $data): SessionInterface;
}
