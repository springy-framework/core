<?php
/**
 * Parent class for web controllers.
 *
 * Extends this class to construct web controllers in the applications.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

use Springy\Core\ControllerInterface;
use Springy\Exceptions\Http403Error;
use Springy\Exceptions\SpringyException;
use Springy\Security\AclManager;

class Controller implements ControllerInterface
{
    /** @var AclManager the ACL manager object */
    protected $aclManager;
    /** @var bool the controller is restricted to signed in users */
    protected $authNeeded = false;
    /** @var bool the controller is forbidden for the caller */
    protected $hasPermission;
    /** @var mixed */
    protected $redirectUnsigned = false;
    /** @var AclUserInterface the current user object */
    protected $user;

    /**
     * The constructor method.
     */
    public function __construct(array $segments)
    {
        $this->user = $this->_getAuthManager();
        $this->aclManager = new AclManager($this->user, $this, $segments);
        $this->hasPermission = $this->_hasAuthorization();
    }

    /**
     * Checks the user permission for the called method.
     *
     * @return bool
     */
    protected function _hasAuthorization(): bool
    {
        // Authorize if unsigned users has access
        if (!$this->authNeeded) {
            return true;
        }

        return $this->user->getId()
            && $this->_userSpecialVerifications()
            && $this->aclManager->hasPermission();
    }

    /**
     * Tries to get the authentication manager object.
     *
     * @throws SpringyException
     *
     * @return AclUserInterface
     */
    protected function _getAuthManager()
    {
        try {
            $authManager = app('user.auth.manager');
            if ($authManager->check()) {
                return $authManager->user();
            }
        } catch (\Throwable $th) {
        }

        try {
            $authIdentity = app('user.auth.identity');
        } catch (\Throwable $th) {
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
    protected function _userSpecialVerifications()
    {
        return true;
    }

    /**
     * Throws a HTTP "403 - Forbidden" error or redirects the user to another page.
     *
     * @throws Http403Error
     *
     * @return void
     */
    public function _forbidden()
    {
        throw new Http403Error();
    }

    /**
     * Checks whether the user has permission to the resource.
     *
     * @return bool
     */
    public function _hasPermission(): bool
    {
        return $this->hasPermission;
    }
}
