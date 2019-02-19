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

class StringUtilsTest extends TestCase
{
    use Springy\Utils\StringUtils;

    public function testEmailGetsValidateSuccessfully()
    {
        $this->assertTrue($this->isValidEmailAddress('fernando@fval.com.br'));
        $this->assertTrue($this->isValidEmailAddress('fernando@fval.com.br', false));

        $this->assertFalse($this->isValidEmailAddress('fernando@fval', false));
        $this->assertFalse($this->isValidEmailAddress('fernandofval.com.br', false));
        $this->assertFalse($this->isValidEmailAddress('fernando@fval.nonexiuuste'));
        $this->assertTrue($this->isValidEmailAddress('fernando@fval.nonexiuuste', false));
    }
}
