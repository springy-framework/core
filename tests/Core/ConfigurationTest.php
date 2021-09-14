<?php
/**
 * Test case for Springy\Core\Configuration class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Configuration;

class ConfigurationTest extends TestCase
{
    protected const BAR_FOO = 'bar.foo';
    protected const FOO_BAR = 'foo.bar';
    protected const FOO_HOMER = 'foo.homer';

    public $conf;

    protected function setUp(): void
    {
        $this->conf = Configuration::getInstance(__DIR__ . '/../conf', 'test', self::FOO_BAR);
    }

    public function testConfigHost()
    {
        $this->assertEquals(self::FOO_BAR, $this->conf->configHost());
        $this->assertEquals(self::BAR_FOO, $this->conf->configHost(self::BAR_FOO));
    }

    public function testConfigPath()
    {
        $this->conf->setPath(__DIR__);
        $this->assertEquals(__DIR__, $this->conf->getPath());
    }

    public function testGet()
    {
        $this->assertEquals(1989, $this->conf->get('foo.simpsons', 1989));
        $this->assertEquals('Doh!', $this->conf->get(self::FOO_BAR));
        $this->assertEquals('Doe', $this->conf->get('foo.john'));
        $this->assertEquals('Haha!', $this->conf->get('foo.nelson'));
        $this->assertEquals('Chup chup!', $this->conf->get('foo.maggie'));
        $this->assertEquals('Oh Homer!', $this->conf->get('foo.marggie'));
        $this->assertEquals('jazz', $this->conf->get('foo.lisa'));
        $this->assertEquals(1, $this->conf->get('foo.bart.grade'));
        $this->assertEquals('beer', $this->conf->get(self::FOO_HOMER));
    }

    public function testGetEnvironment()
    {
        $this->assertEquals('test', $this->conf->getEnvironment());
    }

    public function testSet()
    {
        $this->assertEquals('beer', $this->conf->get(self::FOO_HOMER));
        $this->conf->set(self::FOO_HOMER, 'food');
        $this->assertEquals('food', $this->conf->get(self::FOO_HOMER));
    }

    public function testSetEnvironment()
    {
        $this->conf->setEnvironment('foo');
        $this->assertEquals('foo', $this->conf->getEnvironment());
    }

    public function testSave()
    {
        $this->conf->set('simpsons.grampa', 'old');
        $this->conf->set('simpsons.dog', 'Sant\'s Little Helper');
        $this->conf->save('simpsons');

        $fileName = $this->conf->getPath() . DS . $this->conf->getEnvironment() . DS . 'simpsons.json';

        $this->assertFileExists($fileName);
        unlink($fileName);
    }
}
