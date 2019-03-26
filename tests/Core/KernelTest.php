<?php
/**
 * Test case for Springy\Core\Kernel class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Kernel;

class KernelTest extends TestCase
{
    public $conf;
    public $kernel;

    public function setUp()
    {
        $this->conf = require __DIR__.'/../conf/main.php';
        $this->kernel = Kernel::getInstance();
    }

    public function testConfiguration()
    {
        $this->assertInstanceOf(
            Springy\Core\Configuration::class,
            $this->kernel->configuration()
        );
    }

    public function testErrorHandler()
    {
        $this->assertInstanceOf(Springy\Exceptions\Handler::class, $this->kernel->errorHandler());
    }

    public function testGetEnvironment()
    {
        $this->assertEquals($this->conf['environment'], $this->kernel->getEnvironment());
    }

    public function testApplicationDetails()
    {
        $this->assertEquals($this->conf['app']['name'], $this->kernel->getApplicationName());

        $this->assertEquals(
            implode('.', $this->conf['app']['version']),
            $this->kernel->getApplicationVersion()
        );

        $this->assertEquals($this->conf['app']['code_name'], $this->kernel->getAppCodeName());
    }

    public function testHttpRequest()
    {
        $this->assertInstanceOf(Springy\HTTP\Request::class, $this->kernel->httpRequest());
    }

    public function testSetEnvironment()
    {
        $this->kernel->setEnvironment('testcase');
        $this->assertEquals('testcase', $this->kernel->getEnvironment());

        // Test environment configuration by host or cli alias
        $this->kernel->setEnvironment('', [
            'console' => 'testcase',
        ]);
        $this->assertEquals('testcase', $this->kernel->getEnvironment());

        // Test environment configuration by setting empty
        $this->kernel->setEnvironment('', []);
        $this->assertEquals('console', $this->kernel->getEnvironment());
    }
}
