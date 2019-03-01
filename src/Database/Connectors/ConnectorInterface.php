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

interface ConnectorInterface
{
    /**
     * Gets the DSN string.
     *
     * @return string
     */
    public function getDsn(): string;
}
