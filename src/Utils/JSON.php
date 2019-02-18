<?php
/**
 * Class to construct and send JSON objects.
 *
 * @copyright 2018 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Lucas Cardozo <lucas.cardozo@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version	  1.2.0
 */

namespace Springy\Utils;

use Springy\HTTP\Response;
use Springy\Core\Kernel;


class JSON
{
    /** @var array the JSON data */
    protected $data;
    /** @var int the HTTP response status code */
    protected $statusCode;

    /**
     * Constructor.
     *
     * @param array $data
     * @param int   $status
     */
    public function __construct(array $data = null, int $status = 200)
    {
        $this->data = $data ?? [];
        $this->statusCode = $status;
    }

    /**
     * Magic method to converts object to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->fetch();
    }

    /**
     * Adds a pair key => value to the json data.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function add($key, $value = null)
    {
        if (is_array($key)) {
            $this->merge($key);

            return;
        }

        $this->data = ArrayUtils::newInstance()->add($this->data, $key, $value);
    }

    /**
     * Gets the data array.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Gets the header status code.
     *
     * @return int
     */
    public function getHeaderStatus(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns a JSON string representation of the data.
     *
     * @return string
     */
    public function fetch(): string
    {
        return json_encode($this->data);
    }

    /**
     * Merges the given array into json data
     *
     * @param array $data
     *
     * @return void
     */
    public function merge(array $array)
    {
        $this->data = array_merge($this->data, $array);
    }

    /**
     * Sends the JSON content to the Response.
     *
     * @return void
     */
    public function send()
    {
        $response = Response::getInstance();

        // Set the header
        $response->header()->httpResponseCode($this->statusCode);
        $response->header()->clear();
        $response->header()->contentType('application/json', Kernel::getInstance()->getCharset(), true);
        $response->header()->expires('0');
        $response->header()->lastModified(gmdate(DATE_RFC822));
        $response->header()->cacheControl('no-store, no-cache, must-revalidate');
        $response->header()->cacheControl('post-check=0, pre-check=0', false);
        $response->header()->pragma('no-cache');

        $response->body($this->fetch());
    }

    /**
     * Sets the data array overwriting all previous data.
     *
     * @param array $data
     *
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Sets new status code to the response.
     *
     * @param int $status
     *
     * @return void
     */
    public function setHeaderStatus(int $status)
    {
        $this->statusCode = $status;
    }
}
