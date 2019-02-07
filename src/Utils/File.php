<?php
/**
 * File system handler.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Utils;

use finfo;
use InvalidArgumentException;
use SplFileInfo;
use Springy\Exceptions\SpringyException;

class File extends SplFileInfo
{
    /**
     * Constructor.
     *
     * @param string $filename  full pathname of the file.
     * @param bool   $checkFile checks if the file exists.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $filename, bool $checkFile = true)
    {
        if ($checkFile && !is_file($filename)) {
            throw new InvalidArgumentException();
        }

        parent::__construct($filename);
    }

    /**
     * Gets the original name of the file.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getName(string $name): string
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strpos($originalName, '/');
        $originalName = $pos === false ? $originalName : substr($originalName, $pos + 1);

        return $originalName;
    }

    /**
     * Creates a self object that will be used to future move.
     *
     * @param string $directory the destination folder.
     * @param string $name      new name for the file.
     *
     * @throws SpringyException
     *
     * @return self
     */
    protected function getTargetFile(string $directory, string $name = null): self
    {
        if (!is_dir($directory) && (@mkdir($directory, 0777, true) === false)) {
            throw new SpringyException(sprintf('Unable to create the "%s" directory', $directory));
        } elseif (!is_writable($directory)) {
            throw new SpringyException(sprintf('Unable to write in the "%s" directory', $directory));
        }

        $target = rtrim($directory, '/\\').DS.($name === null ? $this->getBasename() : $this->getName($name));

        return new self($target, false);
    }

    /**
     * Gets the extension of the file.
     *
     * @return string
     */
    public function getExtension(): string
    {
        return pathinfo($this->getBasename(), PATHINFO_EXTENSION);
    }

    /**
     * Gets the MIME type of the file.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($this->getPathname());
    }

    /**
     * Moves the file to another folder and/or name then returns a new self object with it.
     *
     * @param string $path
     * @param string $name
     *
     * @throws SpringyException
     *
     * @return self
     */
    public function moveTo(string $path, string $name = null)
    {
        $target = $this->getTargetFile($path, $name);

        if (!@rename($this->getPathname(), $target)) {
            $error = error_get_last();

            throw new SpringyException(
                sprintf(
                    'Could not move the file "%s" to "%s" (%s)',
                    $this->getPathname(),
                    $target,
                    strip_tags($error['message'])
                )
            );
        }

        @chmod($target, 0666 & ~umask());

        return $target;
    }
}
