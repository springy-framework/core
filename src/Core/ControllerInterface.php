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
     * Throws a HTTP "403 - Forbidden" error.
     *
     * @throws Exception
     *
     * @return void
     */
    public function _forbidden();

    /**
     * Checks whether the user has permission to the resource.
     *
     * @return bool
     */
    public function _hasPermission(): bool;
}
