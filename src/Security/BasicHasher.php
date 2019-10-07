<?php

/**
 * Basic hash generator.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Security;

/**
 * Basic hash generator.
 */
class BasicHasher implements HasherInterface
{
    // Salt to difficult the hash to be broken
    public const SALT = '865516de75706d3e9f8cdae8f66f0e0c15d6ceed';

    /**
     * Creates and returns the generated hash of the entered string.
     *
     * @param string $stringToHash
     * @param int    $times
     *
     * @return string
     */
    public function make(string $stringToHash, int $times = 0): string
    {
        $md5 = md5(strtolower(self::SALT . $stringToHash));

        // Not used
        $times = null;

        return base64_encode($md5 ^ md5($stringToHash));
    }

    /**
     * Checks whether the string needs to be encrypted again.
     *
     * @param string $hash
     * @param int    $times
     *
     * @return bool
     */
    public function needsRehash(string $hash, int $times = 0): bool
    {
        // Not used
        $times = null;

        return $hash === '';
    }

    /**
     * Checks a password against a hash.
     *
     * @param string $stringToCheck
     * @param string $hash
     *
     * @return bool
     */
    public function verify(string $stringToCheck, string $hash): bool
    {
        return $this->make($stringToCheck, 0) === $hash;
    }
}
