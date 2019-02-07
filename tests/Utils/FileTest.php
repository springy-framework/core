<?php
/**
 * Test case for Springy\Utils\File class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use finfo;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;
use Springy\Utils\File;

class FileTest extends TestCase
{
    public $file;

    public function setUp()
    {
        $this->file = new File(__FILE__);
    }

    public function testConstructor()
    {
        $file = new File(__FILE__);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testGetExtension()
    {
        $ext = pathinfo(__FILE__, PATHINFO_EXTENSION);
        $this->assertEquals($ext, $this->file->getExtension());
    }

    public function testGetMimeType()
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $this->assertEquals(
            $finfo->file(__FILE__),
            $this->file->getMimeType()
        );
    }

    public function testMoveTo()
    {
        $path = __DIR__.'/../tmp';
        if (!is_dir($path) && !mkdir($path)) {
            throw new RuntimeException('Can\'t create temporary folder.');
        }

        $fileName = $path.'/test.txt';
        if (file_put_contents($fileName, 'test') === false) {
            throw new RuntimeException('Can\'t write to temporary file.');
        }

        $targetName = $path.'/target.txt';
        $file = new File($fileName);
        $this->assertEquals($targetName, $file->moveTo($path, 'target.txt'));

        if (is_file($targetName)) {
            @unlink($targetName);
        }
        if (is_file($fileName)) {
            @unlink($fileName);
        }
        if (is_dir($path)) {
            @rmdir($path);
        }
    }
}
