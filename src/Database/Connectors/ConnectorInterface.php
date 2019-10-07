<?php

/**
 * Interface to standardize the DMBS connector drivers.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Connectors;

/**
 * Interface to standardize the DMBS connector drivers.
 */
interface ConnectorInterface
{
    /**
     * Returns the name of function to get current date and time from DBMS.
     *
     * @return string
     */
    public function getCurrDate(): string;

    /**
     * Gets the DSN string.
     *
     * @return string
     */
    public function getDsn(): string;
}
