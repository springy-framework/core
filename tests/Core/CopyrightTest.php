<?php
/**
 * Test case for Springy\Core\Copyright class.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Copyright;

class CopyrightTest extends TestCase
{
    public function testContent()
    {
        $copyright = new Copyright();
        $this->assertStringStartsWith('<!DOCTYPE html>', $copyright->content());
    }
}
