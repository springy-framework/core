<?php
/**
 * Interface to standardize the controllers of the application.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Core;

interface ControllerInterface
{
    /**
     * Checks whether the user has permission to the resource.
     *
     * @return bool
     */
    public function _hasPermission(): bool;
}
