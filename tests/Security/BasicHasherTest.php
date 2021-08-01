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
    public $hash;

    protected function setUp(): void
    {
        $this->hasher = new BasicHasher();
        $this->hash = $this->hasher->make('password', 1);
    }

    public function testGenerateHash()
    {


        $this->assertGreaterThanOrEqual(44, strlen($this->hash));
        $this->assertStringEndsWith('=', $this->hash);
    }

    public function testNeedsRehash()
    {
        $this->assertFalse($this->hasher->needsRehash($this->hash, 1));
    }

    public function testVerify()
    {
        $this->assertTrue($this->hasher->verify('password', $this->hash));
    }
}
