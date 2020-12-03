<?php
/**
 * Test case for Springy\Security\BCryptHasher class.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Security\BCryptHasher as Hasher;

class HasherTest extends TestCase
{
    public $hasher;

    protected function setUp(): void
    {
        $this->hasher = new Hasher();
    }

    public function testThatHasherCanGenerateASecureHash()
    {
        $hash = $this->hasher->make('password');

        $this->assertGreaterThanOrEqual(60, strlen($hash));
        $this->assertStringStartsWith('$2y$', $hash);
    }

    public function testThatHasherTellsIfAHashNeedsRehashing()
    {
        $hash = $this->hasher->make('password', 5);

        $this->assertTrue($this->hasher->needsRehash($hash, 10));
    }

    public function testThatHasherCanVerifyTheHashedString()
    {
        $hash = $this->hasher->make('password');

        $this->assertTrue($this->hasher->verify('password', $hash));
    }
}
