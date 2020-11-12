<?php
/**
 * Test case for Springy\HTTP\UploadedFile class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\HTTP\UploadedFile;

require __DIR__ . '/../mocks/mockUploadedFile.php';

class UploadedFileTest extends TestCase
{
    public $mimeType;
    public $originalName;
    public $size;
    public $tmpFolder;
    public $tmpName;
    public $ulFile;

    public function setUp()
    {
        $this->tmpFolder = sys_get_temp_dir();

        $this->tmpName = $this->tmpFolder . '/php68up19';
        if (file_put_contents($this->tmpName, 'test') === false) {
            throw new RuntimeException('Can\'t write to temporary file.');
        }

        $this->mimeType = 'text/plain';
        $this->originalName = 'upload-test.txt';
        $this->size = filesize($this->tmpName);

        $_FILES = [
            'file' => [
                'tmp_name' => $this->tmpName,
                'name'     => 'test.txt',
                'type'     => $this->mimeType,
                'size'     => $this->size,
                'error'    => UPLOAD_ERR_OK,
            ],
        ];

        $this->ulFile = new UploadedFile(
            $this->tmpName,
            $this->originalName,
            $this->mimeType,
            $this->size,
            UPLOAD_ERR_OK
        );
    }

    public function tearDown()
    {
        unset($_FILES);

        if (is_file($this->tmpName)) {
            @unlink($this->tmpName);
        }
    }

    public function testGetErrorCode()
    {
        $this->assertEquals(
            UPLOAD_ERR_OK,
            $this->ulFile->getErrorCode()
        );
    }

    public function testGetErrorMessage()
    {
        $this->assertStringStartsWith(
            'No error',
            $this->ulFile->getErrorMessage()
        );
    }

    public function testIsValid()
    {
        $this->assertTrue($this->ulFile->isValid());
    }

    public function testGetOriginalExtension()
    {
        $this->assertEquals(
            pathinfo($this->originalName, PATHINFO_EXTENSION),
            $this->ulFile->getOriginalExtension()
        );
    }

    public function testGetOriginalMimeType()
    {
        $this->assertEquals(
            $this->mimeType,
            $this->ulFile->getOriginalMimeType()
        );
    }

    public function testGetOriginalName()
    {
        $this->assertEquals(
            $this->originalName,
            $this->ulFile->getOriginalName()
        );
    }

    public function testGetOriginalSize()
    {
        $this->assertEquals(
            $this->size,
            $this->ulFile->getOriginalSize()
        );
    }

    public function testMoveTo()
    {
        $targetName = $this->tmpFolder . '/target.txt';

        $this->assertEquals($targetName, $this->ulFile->moveTo($this->tmpFolder, 'target.txt'));

        if (is_file($targetName)) {
            @unlink($targetName);
        }
    }

    public function testGetMaxFilesize()
    {
        $this->assertGreaterThan(0, UploadedFile::getMaxFilesize());
    }

    public function testArrayToUploadedFiles()
    {
        $files = UploadedFile::arrayToUploadedFiles($_FILES);

        $this->assertCount(1, $files);
        $this->assertInstanceOf(UploadedFile::class, $files['file']);
        $this->assertTrue($files['file']->isValid());
    }
}
