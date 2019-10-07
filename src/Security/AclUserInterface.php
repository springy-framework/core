<?php

/**
 * Interface to standardize the identities that will be allowed in the application.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   2.0.0
 */

namespace Springy\Security;

/**
 * Interface to standardize the identities that will be allowed in the application.
 */
interface AclUserInterface
{
    /**
     * Get the user permission for the given ACL.
     *
     * @param string $aclObjectName the name of the ACL.
     *
     * @return bool True if the user has permission to access or false if not.
     */
    public function hasPermissionFor(string $aclObjectName): bool;
}
