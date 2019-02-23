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
use Springy\Template\Drivers\Mustache;
use Springy\Template\Drivers\Smarty;
use Springy\Template\Drivers\Twig;
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

        $this->assertInstanceOf(Smarty::class, $template->getTemplateDriver());
        $this->assertTrue($template->templateExists());
        $this->assertFalse($template->isCached());
        $this->assertEquals('Foo', $template->fetch());

        $template->clearCompileDir();
    }

    public function testTwigTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('tpl-twig');

        $template = new Template('test');
        $template->assign('test', 'Bar');

        $this->assertInstanceOf(Twig::class, $template->getTemplateDriver());
        $this->assertTrue($template->templateExists());
        $this->assertEquals('Test Bar', $template->fetch());

        $template->clearCache();

        $dir = scandir(config_get('template.paths.cache'));
        $this->assertEquals(['.', '..'], $dir);
    }

    public function testMustacheTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('tpl-mustache');

        $template = new Template('test');
        $template->assign('test', 'bar');

        $this->assertInstanceOf(Mustache::class, $template->getTemplateDriver());
        $this->assertTrue($template->templateExists());
        $this->assertEquals('Foo bar', $template->fetch());

        // $template->clearCache();

        $dir = scandir(config_get('template.paths.cache'));
        $this->assertEquals(['.', '..'], $dir);
    }
}
