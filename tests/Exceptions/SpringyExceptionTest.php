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
    public function testContext()
    {
        $err = new SpringyException('test case', E_USER_ERROR, __FILE__, __LINE__, ['test']);

        $this->assertCount(1, $err->getContext());
    }
}
