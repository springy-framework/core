<?php

/**
 * Test case for Springy\Security\AclManager class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

use PHPUnit\Framework\TestCase;
use Springy\Core\ControllerInterface;
use Springy\Security\AclManager;

require_once __DIR__ . '/../mocks/mockUser.php';

class AclManagerTest extends TestCase
{
    public $aclManager;

    protected function setUp(): void
    {
        $controller = new Controller();
        $this->aclManager = new AclManager(new User(), $controller, ['test', 'acl']);
    }

    public function testGetAclObjectName()
    {
        $this->assertEquals(
            'Controller|test|acl',
            $this->aclManager->getAclObjectName()
        );
    }

    public function testGetAclUser()
    {
        $this->assertInstanceOf(User::class, $this->aclManager->getAclUser());
    }

    public function testGetSeparator()
    {
        $this->assertEquals('|', $this->aclManager->getSeparator());
    }

    public function testHasPermission()
    {
        $this->assertFalse($this->aclManager->hasPermission());
    }

    public function testSetAclUser()
    {
        $otherUser = new OtherUser();
        $this->aclManager->setAclUser($otherUser);
        $this->assertInstanceOf(OtherUser::class, $this->aclManager->getAclUser());
        $this->assertTrue($this->aclManager->hasPermission());
    }

    public function testSetSeparator()
    {
        $this->aclManager->setSeparator(';');
        $this->assertEquals(';', $this->aclManager->getSeparator());
        $this->assertFalse($this->aclManager->hasPermission());
    }
}

class Controller implements ControllerInterface
{
    public function hasPermission(): bool
    {
        return true;
    }
}

class OtherUser extends User
{
    public function hasPermissionFor(string $aclObjectName): bool
    {
        return $aclObjectName === 'Controller|test|acl';
    }
}
