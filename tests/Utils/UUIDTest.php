<?php
/**
 * Test case for Springy\Utils\UUID class.
 *
 * @copyright 2015 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version    1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Utils\UUID;

class UUIDTest extends TestCase
{
    protected const UUID_REGEX = '/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/';

    public function testRandomUUID()
    {
        $uuid1 = UUID::random();
        $uuid2 = UUID::random();
        $this->assertMatchesRegularExpression(self::UUID_REGEX, $uuid1);
        $this->assertMatchesRegularExpression(self::UUID_REGEX, $uuid2);
        $this->assertNotEquals($uuid1, $uuid2);
    }

    public function testV3UUID()
    {
        $name = 'test';
        $uuid = UUID::random();
        $v3_1 = UUID::v3($uuid, $name);
        $v3_2 = UUID::v3($uuid, $name);

        $this->assertMatchesRegularExpression(self::UUID_REGEX, $v3_1);
        $this->assertMatchesRegularExpression(self::UUID_REGEX, $v3_2);
        $this->assertEquals($v3_1, $v3_2);
    }

    public function testV4UUID()
    {
        $uuid1 = UUID::v4();
        $uuid2 = UUID::v4();
        $this->assertMatchesRegularExpression(self::UUID_REGEX, $uuid1);
        $this->assertMatchesRegularExpression(self::UUID_REGEX, $uuid2);
        $this->assertNotEquals($uuid1, $uuid2);
    }
}
