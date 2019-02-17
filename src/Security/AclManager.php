<?php
/**
 * ACL (Access Control List) Authorization class for the web application.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Security;

use Springy\Core\Kernel;

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
     */
    public function __construct(AclUserInterface $user)
    {
        $this->user = $user;

        $kernel = Kernel::getInstance();
        $controller = $kernel->controller();
        $parameters = $kernel->parameters();
        if ($controller === null) {
            return;
        }

        $this->module = array_merge(
            explode('\\', get_class($controller)),
            $parameters
        );
    }

    /**
     * Defines current ACL object.
     *
     * @return void
     */
    private function setupCurrentAclObject()
    {
        $this->module = substr(
            Kernel::controllerNamespace(),
            strlen($this->modulePrefix)
        ) or $this->defaultModule;
        $this->controller = URI::getControllerClass();
        // $this->action = URI::getSegment(0);

        $segments = [];
        $ind = 0;
        do {
            $segment = URI::getSegment($ind++);
            if ($segment !== false) {
                $segments[] = $segment;
            }
        } while ($segment !== false);

        $this->action = implode($this->separator, $segments);
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
