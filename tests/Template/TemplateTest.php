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
    protected const NO_NAME = 'no name';

    public function testSmartyTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('tpl-smarty');

        $template = new Template('test');
        $template->assign('test', 'Foo');
        $template->addFunction('personal', function ($options) {
            return $options['name'] ?? self::NO_NAME;
        });

        $this->assertInstanceOf(Smarty::class, $template->getTemplateDriver());
        $this->assertTrue($template->templateExists());
        $this->assertFalse($template->isCached());
        $this->assertEquals('Smarty Foo Bar', $template->fetch());

        $template->clearCompileDir();
    }

    public function testTwigTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('tpl-twig');

        $template = new Template('test');
        $template->assign('test', 'Foo');
        $template->addFunction('personal', function ($text) {
            return $text ?? self::NO_NAME;
        });

        $this->assertInstanceOf(Twig::class, $template->getTemplateDriver());
        $this->assertTrue($template->templateExists());
        $this->assertEquals('Twig Foo Bar', $template->fetch());

        $template->clearCache();

        $dir = scandir(config_get('template.paths.cache'));
        $this->assertEquals(['.', '..'], $dir);
    }

    public function testMustacheTemplateDriver()
    {
        Kernel::getInstance()->setEnvironment('tpl-mustache');

        $template = new Template('test');
        $template->assign('test', 'Foo');
        $template->addFunction('personal', function ($text) {
            return $text ?? self::NO_NAME;
        });

        $this->assertInstanceOf(Mustache::class, $template->getTemplateDriver());
        $this->assertTrue($template->templateExists());
        $this->assertEquals('Mustache Foo Bar', $template->fetch());

        $dir = scandir(config_get('template.paths.cache'));
        $this->assertEquals(['.', '..'], $dir);
    }
}
