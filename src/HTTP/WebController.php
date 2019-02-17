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

use Springy\Exceptions\SpringyException;
use Springy\Security\AclManager;

class WebController
{
    /** @var AclManager the ACL manager object */
    protected $aclManager;
    /** @var bool the controller is restricted to signed in users */
    protected $authNeeded = false;
    /** @var AclUserInterface the current user object */
    protected $user;

    /**
     * The constructor method.
     */
    public function __construct()
    {
        $this->aclManager = new AclManager($this->_getAuthManager());
        $this->$user = $this->aclManager->getAclUser();

        // Do nothing if is free for unsigned users
        if (!$this->authNeeded) {
            return;
        }

        // Verify if is an authenticated user
        if ($this->user->isLoaded()) {
            // Call user special verifications
            if (!$this->_userSpecialVerifications()) {
                $this->_forbidden();
            }

            // Check if the controller and respective method is permitted to the user
            $this->_authorizationCheck();

            return;
        }

        // Kill the application with the 403 forbidden page.
        $this->_forbidden();
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
}
