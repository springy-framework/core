<?php
/**
 * Trait with network helper functions.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version	  1.0.0
 *
 * The methods of this trait can be accessed by seting use
 * of this trait inside user classes.
 *
 * @see http://php.net/manual/pt_BR/language.oop5.traits.php
 */

namespace Springy\Utils;

trait NetworkUtils
{
    /**
     * Gets the real remote address.
     *
     * This method attempts to retrieve the actual IP of the visitor by
     * doing checks and ensuring that no invalid IP value is returned.
     *
     * There are certain situations where the visitor's real IP
     * is masked when the application server is behind a firewall
     * or load balancer. In these cases it is necessary to make certain
     * place only the value of the variable $_SERVER['REMOTE_ADDR'].
     *
     * @return string
     */
    protected function getRealRemoteAddr(): string
    {
        // Check if behind a proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown')) {
            foreach (explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $val) {
                $val = trim($val);

                if ($this->isValidIP($val)) {
                    return $val;
                }
            }
        }

        // Check header HTTP_X_REAL_IP or HTTP_CLIENT_IP
        $remoteIp = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? '';
        if ($this->isValidIP(trim($remoteIp))) {
            return trim($remoteIp);
        }

        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /**
     * Verifies if a IP is from a local area network.
     *
     * @param string $userIP
     *
     * @return bool
     */
    protected function isPraviteNetwork(string $userIP): bool
    {
        // 10.0.0.0/8 or 192.168.0.0/16
        if (substr($userIP, 0, 3) == '10.' || substr($userIP, 0, 8) == '192.168.') {
            return true;
        }

        // 172.16.0.0/12
        if (substr($userIP, 0, 4) == '172.') {
            $oct = (int) trim(substr($userIP, 4, 3), '.');
            if ($oct >= 16 && $oct <= 31) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifies if given IP is valid.
     *
     * @param string $ipValue
     *
     * @return bool
     */
    protected function isValidIP(string $ipValue): bool
    {
        if (filter_var($ipValue, FILTER_VALIDATE_IP) === false
            || $this->isPraviteNetwork($ipValue)
            || !strcasecmp($ipValue, 'unknown')) {
            return false;
        }

        return true;
    }
}
