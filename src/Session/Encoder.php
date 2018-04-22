<?php

declare(strict_types=1);

namespace Jasny\Session;

/**
 * Encode data to store as session.
 * 
 * @copyright (c) 2017 PSR7 Sessions (MIT license)
 * @link https://github.com/psr7-sessions/session-encode-decode
 */
class Encoder
{
    /**
     * Invoke encoder
     * 
     * @param array $sessionData
     * @return string
     */
    public function __invoke(array $sessionData): string
    {
        if (empty($sessionData)) {
            return '';
        }

        $encodedData = '';

        foreach ($sessionData as $key => $value) {
            $encodedData .= $key . '|' . serialize($value);
        }

        return $encodedData;
    }
}
