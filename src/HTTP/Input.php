<?php

/**
 * GET and FORM-DATA POST input data header.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

use Springy\Utils\ArrayUtils;

/**
 * GET and FORM-DATA POST input data header.
 */
class Input
{
    // Constant for old data session key
    protected const OLD_DATA_SESSION = '__OLDINPUT__';

    /** @var ArrayUtils array handler helper */
    protected $arrUtils;
    /** @var array collection of data received */
    protected $data;
    /** @var array collection of UploadedFile handlers */
    protected $files;
    /** @var array old data array saved to be used in next request */
    protected $oldData;
    /** @var string the request method */
    protected $requestMethod;
    /** @var bool the request method is an Ajax */
    protected $ajaxRequest;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->arrUtils = new ArrayUtils();

        // Joins the $_POST and $_GET magic arrays with priority to $_POST
        $this->data = $this->sanitizeInputData($_POST) + $this->sanitizeInputData($_GET);

        // Build the UploadedFile object with files sent by multipart form-data POST
        $this->files = UploadedFile::arrayToUploadedFiles($_FILES);

        $request = Request::getInstance();
        $this->requestMethod = $request->getMethod();
        $this->ajaxRequest = $request->isAjax();

        // Loads the data saved in session in the last request and clear the session
        $session = Session::getInstance();
        $this->oldData = $session->get(self::OLD_DATA_SESSION, []);
        $session->forget(self::OLD_DATA_SESSION);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->storeForNextRequest();
    }

    /**
     * Satinizes input data array for secure use.
     *
     * @param array $data
     *
     * @return array
     */
    protected function sanitizeInputData(array $data): array
    {
        $sanitizedData = [];

        foreach ($data as $key => $value) {
            $sanitizedData[$key] = $this->trimData($value);
        }

        return $sanitizedData;
    }

    /**
     * Trims trailing space from data.
     *
     * @param string|array $value
     *
     * @return string|array
     */
    protected function trimData($value)
    {
        if (is_array($value)) {
            return $this->sanitizeInputData($value);
        }

        return trim($value);
    }

    /**
     * Gets all data received in actual request.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Gets all files uploaded.
     *
     * @return array
     */
    public function allFiles(): array
    {
        return $this->files;
    }

    /**
     * Gets all request data except for the keys in given array.
     *
     * @param array $keys
     *
     * @return array
     */
    public function except(array $keys): array
    {
        return $this->arrUtils->except($this->data, $keys);
    }

    /**
     * Gets the file with given key.
     *
     * @param string $key
     *
     * @return UploadedFile|null
     */
    public function file(string $key)
    {
        return $this->arrUtils->dottedGet($this->files, $key);
    }

    /**
     * Gets the data by given key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->arrUtils->dottedGet($this->data, $key, $default);
    }

    /**
     * Checks whether the data with given key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Checks whether the input has a file with the key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]);
    }

    /**
     * Checks whether the request method is an Ajax.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->ajaxRequest;
    }

    /**
     * Checks whether the request method was a POST.
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->requestMethod === 'POST';
    }

    /**
     * Gets the data of the previous request that was saved in session by given key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function old(string $key, $default = null)
    {
        return $this->arrUtils->dottedGet($this->oldData, $key, $default);
    }

    /**
     * Get all data received in the request with given keys.
     *
     * @param array $keys
     *
     * @return mixed
     */
    public function only(array $keys): array
    {
        return $this->arrUtils->only($this->data, $keys);
    }

    /**
     * Saves the current data in session for the next request.
     *
     * @return void
     */
    public function storeForNextRequest()
    {
        if (!empty($this->data)) {
            Session::getInstance()->set(self::OLD_DATA_SESSION, $this->all());
        }
    }
}
