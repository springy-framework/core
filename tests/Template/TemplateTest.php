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

/**
 * @runTestsInSeparateProcesses
 */
class TemplateTest extends TestCase
{
    public function testSmartyTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('tpl-smarty');

        $template = new Template('test');
        $template->assign('test', 'Foo');
        $dir = config_get('template.paths.compiled');

        $this->assertEquals('Foo', $template->fetch());

        $template->clearCompileDir();
    }

    public function testTwigTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('tpl-twig');

        $template = new Template('test');
        $template->assign('test', 'Bar');
        $template->setCaching(false);

        $this->assertEquals('Test Bar', $template->fetch());
    }
}
