<?php
/**
 * Test case for Springy\Security\BasicHasher class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Security\BasicHasher;

class BasicHasherTest extends TestCase
{
    public $hasher;

    public function setUp()
    {
        $this->hasher = new BasicHasher();
    }

    public function testGenerateHash()
    {
        $hash = $this->hasher->make('password', 1);

        $this->assertGreaterThanOrEqual(44, strlen($hash));
        $this->assertStringEndsWith('=', $hash);
    }

    public function testNeedsRehash()
    {
        $hash = $this->hasher->make('password', 1);

        $this->assertFalse($this->hasher->needsRehash($hash, 1));
    }

    public function testVerify()
    {
        $hash = $this->hasher->make('password', 1);

        $this->assertTrue($this->hasher->verify('password', $hash));
    }
}
