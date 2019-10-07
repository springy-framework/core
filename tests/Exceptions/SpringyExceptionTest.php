<?php
/**
 * Test case for Springy\Exceptions\SpringyException class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Exceptions\SpringyException;

class SpringyExceptionTest extends TestCase
{
    public function testException()
    {
        $line = __LINE__ + 1;
        $err = new SpringyException('test case', E_USER_ERROR);

        $this->assertEquals(E_USER_ERROR, $err->getCode());
        $this->assertEquals(__FILE__, $err->getFile());
        $this->assertEquals($line, $err->getLine());
        $this->assertEquals('test case', $err->getMessage());
    }
}
