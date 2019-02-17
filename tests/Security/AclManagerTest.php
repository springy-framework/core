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
use Springy\Security\AclManager;

require_once __DIR__.'/../mocks/mockUser.php';

class AclManagerTest extends TestCase
{
    public $aclManager;

    public function setUp()
    {
        $this->aclManager = new AclManager(new User());
    }

    public function testGetAclObjectName()
    {
        $this->assertEquals('', $this->aclManager->getAclObjectName());
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
        $this->assertTrue($this->aclManager->hasPermission());
    }

    public function testSetAclUser()
    {
        $otherUser = new OtherUser();
        $this->aclManager->setAclUser($otherUser);
        $this->assertInstanceOf(OtherUser::class, $this->aclManager->getAclUser());
    }

    public function testSetSeparator()
    {
        $this->aclManager->setSeparator(';');
        $this->assertEquals(';', $this->aclManager->getSeparator());
    }
}

class OtherUser extends User
{
    public $parent;
}
