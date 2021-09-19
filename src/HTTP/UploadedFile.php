<?php

/**
 * HTTP uploaded files handler.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

use Springy\Exceptions\SpringyException;
use Springy\Utils\File;

/**
 * HTTP uploaded files handler.
 */
class UploadedFile extends File
{
    /** @var string the original name of the uploaded file */
    protected $originalName;
    /** @var string the MIME type of the uploaded file */
    protected $mimeType;
    /** @var int the size of the uploaded file */
    protected $size;
    /** @var int the error code of the upload */
    protected $error;

    /**
     * Constructor.
     *
     * @param string $filename
     * @param string $originalName
     * @param string $mimeType
     * @param int    $size
     * @param int    $error
     */
    public function __construct(
        string $filename,
        string $originalName,
        string $mimeType = null,
        int $size = null,
        int $error = null
    ) {
        if (!ini_get('file_uploads')) {
            throw new SpringyException(
                sprintf(
                    'Unable to create UploadedFile because "file_uploads" is disabled in your php.ini file (%s)',
                    get_cfg_var('cfg_file_path')
                )
            );
        }

        $this->originalName = $this->getName($originalName);
        $this->mimeType = $mimeType ?: 'application/octet-stream';
        $this->size = $size;
        $this->error = $error ?? UPLOAD_ERR_OK;

        parent::__construct($filename, UPLOAD_ERR_OK === $this->error);
    }

    /**
     * Convert an array item to UploadedFile object.
     *
     * @param array $file
     *
     * @return self
     */
    protected static function arrayToUploadedFile(array $file): self
    {
        if (is_array($file['tmp_name'])) {
            $keys = array_keys($file['tmp_name']);
            $files = [];
            foreach ($keys as $key) {
                $files[$key] = [
                    'name'     => $file['name'][$key],
                    'tmp_name' => $file['tmp_name'][$key],
                    'type'     => $file['type'][$key],
                    'size'     => $file['size'][$key],
                    'error'    => $file['error'][$key],
                ];
            }

            return self::arrayToUploadedFiles($files);
        }

        return new self($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
    }

    /**
     * Gets the error code of the upload.
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->error;
    }

    /**
     * Gets the message of the upload error.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        static $errors = [
            UPLOAD_ERR_OK         => 'No error found.',
            UPLOAD_ERR_INI_SIZE   => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d kb).',
            UPLOAD_ERR_FORM_SIZE  => 'The file "%s" exceeds the upload limit defined in your form.',
            UPLOAD_ERR_PARTIAL    => 'The file "%s" was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            UPLOAD_ERR_EXTENSION  => 'File upload was stopped by a PHP extension.',
        ];

        $errorCode = $this->error;
        $maxFilesize = $errorCode === UPLOAD_ERR_INI_SIZE ? self::getMaxFilesize() / 1024 : 0;
        $message = $errors[$errorCode] ?? 'The file "%s" was not uploaded due to an unknown error.';

        return sprintf($message, $this->getOriginalName(), $maxFilesize);
    }

    /**
     * Checks whether the uploaded file is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return ($this->error === UPLOAD_ERR_OK) && is_uploaded_file($this->getPathname());
    }

    /**
     * Gets the extension of the uploaded file.
     *
     * @return string
     */
    public function getOriginalExtension(): string
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    /**
     * Gets the MIME type of the uploaded file.
     *
     * @return string
     */
    public function getOriginalMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Gets the original file name.
     *
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * Gets the size of the uploade file.
     *
     * @return int
     */
    public function getOriginalSize(): int
    {
        return $this->size;
    }

    /**
     * Moves the uploaded file to another folder and/or name then returns a new self object with it.
     *
     * @param string $directory the destination folder.
     * @param string $name      new name for the file.
     *
     * @throws SpringyException
     *
     * @return self
     */
    public function moveTo(string $directory, string $name = null)
    {
        if ($this->isValid()) {
            $target = $this->getTargetFile($directory, $name);

            if (!@move_uploaded_file($this->getPathname(), $target)) {
                $lasterror = error_get_last();

                throw new SpringyException(
                    sprintf(
                        'Could not move the file "%s" to "%s" (%s)',
                        $this->getPathname(),
                        $target,
                        strip_tags($lasterror['message'])
                    )
                );
            }

            @chmod($target, 0666 & ~umask());

            return $target;
        }

        throw new SpringyException($this->getErrorMessage());
    }

    /**
     * Gets the max file size uploadable.
     *
     * @return int
     */
    public static function getMaxFilesize(): int
    {
        $iniMax = strtolower(ini_get('upload_max_filesize'));

        if ('' === $iniMax) {
            return PHP_INT_MAX;
        }

        $max = intval(ltrim($iniMax, '+'));
        $base = 1024;
        $multiplier = [
            'k' => $base,
            'm' => $base ** 2,
            'g' => $base ** 3,
            't' => $base ** 4,
            'p' => $base ** 5,
        ];

        return $max * ($multiplier[substr($iniMax, -1)] ?? 1);
    }

    /**
     * Converts a mutidimentional array like superglobal $_FILES
     * to a collection of UploadedFile objects.
     *
     * @param array $files
     *
     * @return array
     */
    public static function arrayToUploadedFiles(array $files): array
    {
        $convertedFiles = [];

        foreach ($files as $name => $info) {
            $convertedFiles[$name] = self::arrayToUploadedFile($info);
        }

        return $convertedFiles;
    }
}
