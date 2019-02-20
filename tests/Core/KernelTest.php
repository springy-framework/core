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
        $this->conf = require __DIR__.'/../config.php';
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
        $this->assertEquals($this->conf['ENVIRONMENT'], $this->kernel->getEnvironment());
    }

    public function testGetEnvironmentType()
    {
        $this->assertEquals(Kernel::ENV_TYPE_CLI, $this->kernel->getEnvironmentType());
    }

    public function testGetProjectCodeName()
    {
        $this->assertEquals(
            $this->conf['PROJECT_CODE_NAME'],
            $this->kernel->getProjectCodeName()
        );
    }

    public function testGetSystemName()
    {
        $this->assertEquals($this->conf['SYSTEM_NAME'], $this->kernel->getSystemName());
    }

    public function testGetSystemVersion()
    {
        $this->assertEquals(
            implode('.', $this->conf['SYSTEM_VERSION']),
            $this->kernel->GetSystemVersion()
        );
    }

    public function testHttpRequest()
    {
        $this->assertInstanceOf(Springy\HTTP\Request::class, $this->kernel->httpRequest());
    }

    public function testSetEnvironment()
    {
        $this->kernel->setEnvironment('testcase');
        $this->assertEquals('testcase', $this->kernel->getEnvironment());

        // Test environment configuration by env_var
        putenv('ENVIRONMENT=test');
        $this->kernel->setEnvironment('', [], 'ENVIRONMENT');
        $this->assertEquals('test', $this->kernel->getEnvironment());

        // Test environment configuration by host or cli alias
        $this->kernel->setEnvironment('', [
            'cli' => 'testcase',
        ]);
        $this->assertEquals('testcase', $this->kernel->getEnvironment());

        // Test environment configuration by setting empty
        $this->kernel->setEnvironment('', [], '');
        $this->assertEquals('cli', $this->kernel->getEnvironment());
    }

    public function testSetProjectCodeName()
    {
        $this->kernel->setProjectCodeName('Beta');
        $this->assertEquals('Beta', $this->kernel->getProjectCodeName());
    }

    public function testSetSystemName()
    {
        $this->kernel->setSystemName('Springy Test');
        $this->assertEquals('Springy Test', $this->kernel->getSystemName());
    }

    public function testSetSystemVersion()
    {
        $this->kernel->setSystemVersion(1, 0, 1);
        $this->assertEquals('1.0.1', $this->kernel->getSystemVersion());

        $this->kernel->setSystemVersion([1, 0, 2]);
        $this->assertEquals('1.0.2', $this->kernel->getSystemVersion());
    }

    public function testSetUp()
    {
        $this->assertTrue($this->kernel->setUp(__DIR__.'/../config.php'));
        $this->assertEquals('Foo', $this->kernel->getSystemName());
        $this->assertTrue($this->kernel->setUp($this->conf));
    }
}
