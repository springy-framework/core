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
    public $conf;

    protected function setUp(): void
    {
        $this->conf = Configuration::getInstance(__DIR__ . '/../conf', 'test', 'foo.bar');
    }

    public function testConfigHost()
    {
        $this->assertEquals('foo.bar', $this->conf->configHost());
        $this->assertEquals('bar.foo', $this->conf->configHost('bar.foo'));
    }

    public function testConfigPath()
    {
        $this->conf->setPath(__DIR__);
        $this->assertEquals(__DIR__, $this->conf->getPath());
    }

    public function testGet()
    {
        $this->assertEquals(1989, $this->conf->get('foo.simpsons', 1989));
        $this->assertEquals('Doh!', $this->conf->get('foo.bar'));
        $this->assertEquals('Doe', $this->conf->get('foo.john'));
        $this->assertEquals('Haha!', $this->conf->get('foo.nelson'));
        $this->assertEquals('Chup chup!', $this->conf->get('foo.maggie'));
        $this->assertEquals('Oh Homer!', $this->conf->get('foo.marggie'));
        $this->assertEquals('jazz', $this->conf->get('foo.lisa'));
        $this->assertEquals(1, $this->conf->get('foo.bart.grade'));
        $this->assertEquals('beer', $this->conf->get('foo.homer'));
    }

    public function testGetEnvironment()
    {
        $this->assertEquals('test', $this->conf->getEnvironment());
    }

    public function testSet()
    {
        $this->assertEquals('beer', $this->conf->get('foo.homer'));
        $this->conf->set('foo.homer', 'food');
        $this->assertEquals('food', $this->conf->get('foo.homer'));
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
