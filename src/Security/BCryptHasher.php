<?php
/**
 * BCrypt hash generator.
 *
 * This class uses the password_compat class of Anthony Ferrara as a dependency.
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
 * BCrypt hash generator.
 */
class BCryptHasher implements HasherInterface
{
    protected $algorithm;
    protected $salt;

    /**
     * Constructor.
     *
     * @param inte   $algorithm
     * @param string $salt
     */
    public function __construct(int $algorithm = PASSWORD_DEFAULT, string $salt = '')
    {
        $this->algorithm = $algorithm;
        $this->salt = $salt;
    }

    /**
     * Creates and returns the generated hash of the entered string.
     *
     * @param string $stringToHash
     * @param int    $times
     *
     * @return string
     */
    public function make(string $stringToHash, int $times = 10): string
    {
        return password_hash($stringToHash, $this->algorithm, $this->options($times));
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
        return password_verify($stringToCheck, $hash);
    }

    /**
     * Checks whether the string needs to be encrypted again.
     *
     * @param string $hash
     * @param int    $times
     *
     * @return bool
     */
    public function needsRehash(string $hash, int $times = 10): bool
    {
        return password_needs_rehash($hash, $this->algorithm, $this->options($times));
    }

    /**
     * Returns array of options for the BCrypt hash function.
     *
     * @param integer $times number of times the algorithm should be executed.
     *
     * @return array
     */
    protected function options(int $times): array
    {
        $options = ['cost' => $times];

        if ($this->salt) {
            $options['salt'] = $this->salt;
        }

        return $options;
    }
}
