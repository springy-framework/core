<?php

/**
 * Trait with string helper functions.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 *
 * The methods of this trait can be accessed by seting use
 * of this trait inside user classes.
 *
 * @see http://php.net/manual/pt_BR/language.oop5.traits.php
 */

namespace Springy\Utils;

/**
 * Trait with string helper functions.
 */
trait StringUtils
{
    /**
     * Verify that this is a valid email address.
     *
     * @param string $email    the email address.
     * @param bool   $checkDNS determines whether the existence of the email domain should be verified.
     *
     * @return bool
     */
    protected function isValidEmailAddress(string $email, bool $checkDNS = true): bool
    {
        if (
            filter_var($email, FILTER_VALIDATE_EMAIL) &&
            preg_match('/^[a-z0-9_\-]+(\.[a-z0-9_\-]+)*@([a-z0-9_\.\-]*[a-z0-9_\-]+\.[a-z]{2,})$/i', $email, $res)
        ) {
            return $checkDNS ? checkdnsrr($res[2]) : true;
        }

        return false;
    }
}
