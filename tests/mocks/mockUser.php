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
use Springy\Security\BasicHasher;
use Springy\Security\IdentityInterface;

class User implements IdentityInterface, AclUserInterface
{
    public $uuid;
    public $email;
    public $password;

    protected function validPass()
    {
        return (new BasicHasher())->make('Duh!', 0);
    }

    public function loadByCredentials(array $data)
    {

        if ($data['uuid'] == '0001' || $data['email'] == 'homer@springfield.local') {
            $this->uuid = '0001';
            $this->email = $uid;
            $this->password = $this->validPass();

            return;
        }

        $this->uid = '';
        $this->name = '';
        $this->password = '';
    }

    public function fillFromSession(array $data)
    {
    }

    public function getId()
    {
        return $this->uuid;
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
            'uuid'     => $this->uuid,
            'email'    => $this->email,
            'password' => $this->password,
        ];
    }

    public function getCredentials(): array
    {
        return [
            'login'    => 'email',
            'password' => 'password',
        ];
    }

    public function hasPermissionFor(string $aclObjectName): bool
    {
        return $aclObjectName === '';
    }
}
