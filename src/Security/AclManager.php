<?php

/**
 * Access Control List (ACL) Authorization for web application.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Security;

use Springy\Core\ControllerInterface;

/**
 * Access Control List (ACL) Authorization class for the web application.
 */
class AclManager
{
    /** @var array the module */
    protected $module = [];
    /** @var string the permission module name separator character */
    protected $separator = '|';
    /** @var AclUserInterface the current user object */
    protected $user;

    /**
     * Constructor.
     *
     * @param AclUserInterface $user
     * @param array            $segments
     */
    public function __construct(AclUserInterface $user, ControllerInterface $controller, array $segments)
    {
        $this->user = $user;
        $this->module = array_merge(
            explode('\\', get_class($controller)),
            $segments
        );
    }

    /**
     * Gets the ACL string.
     *
     * @return string
     */
    public function getAclObjectName(): string
    {
        return implode($this->separator, $this->module);
    }

    /**
     * Gets the user object.
     *
     * @return AclUserInterface object
     */
    public function getAclUser(): AclUserInterface
    {
        return $this->user;
    }

    /**
     * Gets the separator character used to build the ACL string.
     *
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * Checks whether the current user has permission to access current resource.
     *
     * @return bool
     */
    public function hasPermission(): bool
    {
        return $this->user->hasPermissionFor($this->getAclObjectName());
    }

    /**
     * Defines the user object.
     *
     * @param AclUserInterface $user
     *
     * @return void
     */
    public function setAclUser(AclUserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Defines the separator character to build ACL string.
     *
     * @param string $separator
     *
     * @return void
     */
    public function setSeparator(string $separator)
    {
        $this->separator = $separator;
    }
}
