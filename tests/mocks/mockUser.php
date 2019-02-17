<?php
/**
 * Mock User class for test case for Springy\Security classes.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

use Springy\Security\AclUserInterface;
use Springy\Security\AuthDriverInterface;
use Springy\Security\IdentityInterface;

class User implements IdentityInterface, AclUserInterface
{
    public $uid;
    public $name;

    public function loadByCredentials(array $data)
    {
        $uid = $data[$this->getIdField()] ?? null;

        if ($uid == 'test') {
            $this->uid = $uid;
            $this->name = 'Homer';
        }
    }

    public function fillFromSession(array $data)
    {
    }

    public function getId()
    {
        return $this->uid;
    }

    public function getIdField(): string
    {
        return 'uuid';
    }

    public function getSessionKey(): string
    {
        return 'T35T';
    }

    public function getSessionData(): array
    {
        return [
            'uuid' => $this->uid,
            'name' => $this->name,
        ];
    }

    public function getCredentials(): array
    {
        return [];
    }

    public function hasPermissionFor(string $aclObjectName): bool
    {
        return $aclObjectName === '';
    }
}

class AuthDriver implements AuthDriverInterface
{
    protected $identity;

    public function __construct(IdentityInterface $identity = null)
    {
        $this->setDefaultIdentity($identity);
    }

    public function getIdentitySessionKey(): string
    {
        return $this->identity->getSessionKey();
    }

    public function isValid(string $login, string $password): bool
    {
        return $login === 'Homer' && $password === 'Duh!';
    }

    public function setDefaultIdentity(IdentityInterface $identity)
    {
        $this->identity = $identity;
    }

    public function getDefaultIdentity(): IdentityInterface
    {
    }

    public function getLastValidIdentity(): IdentityInterface
    {
    }

    public function getIdentityById($iid): IdentityInterface
    {
        $this->identity->loadByCredentials([
            $this->identity->getIdField() => $iid,
        ]);

        return $this->identity;
    }
}