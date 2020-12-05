<?php

/**
 * Parent class for web controllers.
 *
 * Extends this class to construct web controllers in the applications.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

use Springy\Core\ControllerInterface;
use Springy\Exceptions\SpringyException;
use Springy\Security\AclManager;
use Throwable;

/**
 * Parent class for web controllers.
 */
class Controller implements ControllerInterface
{
    /** @var \Springy\Security\AclManager the ACL manager object */
    protected $aclManager;
    /** @var bool the controller is restricted to signed in users */
    protected $authNeeded = false;
    /** @var bool the controller is forbidden for the caller */
    protected $hasPermission;
    /** @var mixed */
    protected $redirectUnsigned = false;
    /** @var \Springy\Security\AclUserInterface the current user object */
    protected $user;
    /** @var array */
    protected $uriSegments;

    /**
     * The constructor method.
     */
    public function __construct(array $segments)
    {
        $this->user = $this->getAuthManager();
        $this->aclManager = new AclManager($this->user, $this, $segments);
        $this->hasPermission = $this->hasAuthorization();
        $this->uriSegments = $segments;
    }

    /**
     * Checks the user permission for the called method.
     *
     * @return bool
     */
    protected function hasAuthorization(): bool
    {
        // Authorize if unsigned users has access
        if (!$this->authNeeded) {
            return true;
        }

        return $this->user->getId()
            && $this->userSpecialVerifications()
            && $this->aclManager->hasPermission();
    }

    /**
     * Tries to get the authentication manager object.
     *
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     *
     * @throws SpringyException
     *
     * @return \Springy\Security\AclUserInterface
     */
    protected function getAuthManager()
    {
        try {
            $authManager = app('user.auth.manager');
            if ($authManager->check()) {
                return $authManager->user();
            }
        } catch (Throwable $th) {
        }

        try {
            $authIdentity = app('user.auth.identity');
        } catch (Throwable $th) {
            throw new SpringyException('Authentication driver not configured');
        }

        return $authIdentity;
    }

    /**
     * Does all user special verifications.
     *
     * This method can be extended in child controller to checks special
     * verification you need to do on user account to grant access to the
     * resourse.
     *
     * Example: if you need to checks the user account is blocked.
     *
     * @return bool true if user can access the module or false if not.
     */
    protected function userSpecialVerifications()
    {
        return true;
    }

    /**
     * Checks whether the user has permission to the resource.
     *
     * @return bool
     */
    public function hasPermission(): bool
    {
        return $this->hasPermission;
    }
}
