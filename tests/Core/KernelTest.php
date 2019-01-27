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
        $this->conf = [
            'SYSTEM_NAME'       => 'Springy Test Case',
            'SYSTEM_VERSION'    => [1, 0, 0],
            'PROJECT_CODE_NAME' => 'Alpha',
            'CHARSET'           => 'UTF-8',
            'TIMEZONE'          => 'UTC',
            'ENVIRONMENT'       => 'test',
            'ROOT_PATH'         => __DIR__,
            // 'APP_PATH'          => __DIR__.'/../app',
        ];

        $this->kernel = Kernel::getInstance()->config($this->conf);
    }

    public function testCharset()
    {
        $this->assertEquals($this->conf['CHARSET'], $this->kernel->charset());
        $this->assertEquals('ISO-8859-1', $this->kernel->charset('ISO-8859-1'));
    }

    public function testEnvironment()
    {
        $this->assertEquals($this->conf['ENVIRONMENT'], $this->kernel->environment());
        $this->assertEquals('testcase', $this->kernel->environment('testcase'));

        // Test environment configuration by env_var
        putenv('ENVIRONMENT=test');
        $this->assertEquals('test', $this->kernel->environment('', [], 'ENVIRONMENT'));

        // Test environment configuration by host or cli alias
        $this->assertEquals('testcase', $this->kernel->environment('', [
            'cli' => 'testcase',
        ]));

        // Test environment configuration by setting empty
        $this->assertEquals('cli', $this->kernel->environment('', [], ''));
    }

    public function testErrorHandler()
    {
        $this->assertNull($this->kernel->errorHandler());
    }

    public function testHttpRequest()
    {
        $this->assertNull($this->kernel->httpRequest());
    }

    public function testPaths()
    {
        $this->assertEquals(__DIR__, $this->kernel->path(Kernel::PATH_ROOT));
        $this->assertEquals(__DIR__.'/../', $this->kernel->path(Kernel::PATH_ROOT, __DIR__.'/../'));

        // $this->assertEquals(__DIR__.'/../app', $this->kernel->path(Kernel::PATH_APPLICATION));
        // $this->assertEquals(__DIR__.'/../proj', $this->kernel->path(Kernel::PATH_APPLICATION, __DIR__.'/../proj'));
    }

    public function testProjectCodeName()
    {
        $this->assertEquals($this->conf['PROJECT_CODE_NAME'], $this->kernel->projectCodeName());
        $this->assertEquals('Beta', $this->kernel->projectCodeName('Beta'));
    }

    public function testSystemName()
    {
        $this->assertEquals($this->conf['SYSTEM_NAME'], $this->kernel->systemName());
        $this->assertEquals('Springy Test', $this->kernel->systemName('Springy Test'));
    }

    public function testSystemVersion()
    {
        $this->assertEquals(implode('.', $this->conf['SYSTEM_VERSION']), $this->kernel->systemVersion());
        $this->assertEquals('1.0.1', $this->kernel->systemVersion(1, 0, 1));
        $this->assertEquals('1.0.2', $this->kernel->systemVersion([1, 0, 2]));
    }
}
