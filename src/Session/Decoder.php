<?php

declare(strict_types=1);

namespace Jasny\Session;

/**
 * Decode encoded session data.
 * 
 * @copyright (c) 2017 PSR7 Sessions (MIT license)
 * @link https://github.com/psr7-sessions/session-encode-decode
 */
class Decoder
{
    /**
     * Invoke decoder
     * 
     * @param string $encodedSessionData
     * @return array
     */
    public function __invoke(string $encodedSessionData): array
    {
        if ('' === $encodedSessionData) {
            return [];
        }

        preg_match_all('/(^|;|\})(\w+)\|/i', $encodedSessionData, $matchesarray, PREG_OFFSET_CAPTURE);

        $decodedData = [];

        $lastOffset = null;
        $currentKey = '';
        foreach ($matchesarray[2] as $value) {
            $offset = $value[1];
            if (null !== $lastOffset) {
                $valueText = substr($encodedSessionData, $lastOffset, $offset - $lastOffset);

                /** @noinspection UnserializeExploitsInspection */
                $decodedData[$currentKey] = unserialize($valueText);
            }
            $currentKey = $value[0];

            $lastOffset = $offset + strlen($currentKey) + 1;
        }

        $valueText = substr($encodedSessionData, $lastOffset);

        /** @noinspection UnserializeExploitsInspection */
        $decodedData[$currentKey] = unserialize($valueText);

        return $decodedData;
    }
}
