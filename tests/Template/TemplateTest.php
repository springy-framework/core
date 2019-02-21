<?php
/**
 * Test case for Springy\Template\Template class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Kernel;
use Springy\Template\Template;

class TemplateTest extends TestCase
{
    public function testSmartyTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('template/smarty');

        $template = new Template();

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
          );
    }

    public function testTwigTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('template/twig');

        $template = new Template();

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
          );
    }
}
