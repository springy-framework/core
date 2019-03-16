<?php
/**
 * Exception handler class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

use Exception;
use PDOException;
use Springy\Core\Kernel;
use Springy\HTTP\Response;

class Handler
{
    const HT_ERROR = 1;
    const HT_EXCEPTION = 2;

    /** @var mixed previous error handler */
    protected $prevErrorHandler;
    /** @var mixed previous exception handler */
    protected $prevExceptionHandler;

    /** @var int type of the last handler throwed */
    protected $handlerType;
    /** @var Exception last exception throwed */
    protected $exception;
    /** @var array list of ignored errors */
    protected $ignoredErrors;

    /**
     * Constructor.
     */
    public function __construct()
    {
        error_reporting(E_ALL);
        $this->ignoredErrors = [];
        $this->setHandlers();
    }

    /**
     * Destructor.
     *
     * Restores error and exception handlers if any.
     */
    public function __destruct()
    {
        $this->restoreHandlers();
    }

    /**
     * Displays the application error page.
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     *
     * @return void
     */
    protected function displayError()
    {
        $response = Response::getInstance();

        $response->header()->clear();
        $response->header()->contentType('text/html', 'UTF-8', true);
        $response->header()->pragma('no-cache');
        $response->header()->expires('0');
        $response->header()->cacheControl('must-revalidate, post-check=0, pre-check=0');
        $response->header()->cacheControl('private', false);

        $config = Kernel::getInstance()->configuration();
        $path = $config->get('template.paths.errors').DS.'http'.$response->header()->httpResponseCode().'error.html';

        $body = $this->getErrorName($this->exception->getCode())
            .' - '.$this->exception->getMessage()
            .' on ['.$this->exception->getLine().'] '
            .$this->exception->getFile();

        debug($body);

        if (is_file($path)) {
            $body = file_get_contents($path);
        }

        $response->body($body);
        $response->send($config->get('application.debug'));

        exit(1);
    }

    /**
     * Returns the error name.
     *
     * @param int $errNo
     *
     * @return string
     */
    protected function getErrorName($errNo): string
    {
        $errorNames = [
            E_ERROR             => 'Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Fatal Error',
            1044                => 'Access Denied to Database',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'Deprecated',
            E_RECOVERABLE_ERROR => 'Fatal Error',
        ];

        return $errorNames[$errNo] ?? (
            $this->exception instanceof PDOException
            ? 'Database error ('.$errNo.')'
            : 'Unknown Error ('.$errNo.')'
        );
    }

    /**
     * Shows a 4xx error.
     *
     * @return void
     */
    protected function httpError()
    {
        Response::getInstance()->header()->httpResponseCode($this->exception->getCode());

        $this->displayError();
    }

    /**
     * Restores to previous error and exception handlers.
     *
     * @return void
     */
    protected function restoreHandlers()
    {
        if ($this->prevErrorHandler) {
            restore_error_handler();
            $this->prevErrorHandler = null;
        }

        if ($this->prevExceptionHandler) {
            restore_exception_handler();
            $this->prevExceptionHandler = null;
        }
    }

    /**
     * Adds an error code to the list of ignored errors.
     *
     * @param int|array $error an error code or an array of errors codes.
     *
     * @return void
     */
    public function addIgnoredError($error)
    {
        if (is_array($error)) {
            foreach ($error as $errno) {
                $this->addIgnoredError($errno);
            }

            return;
        }

        if (!in_array($error, $this->ignoredErrors)) {
            $this->ignoredErrors[] = $error;
        }
    }

    /**
     * Removes an error code from the list of ignoded errors.
     *
     * @param int|array $error an error code or an array of errors codes.
     *
     * @return void
     */
    public function delIgnoredError($error)
    {
        if (is_array($error)) {
            foreach ($error as $errno) {
                $this->delIgnoredError($errno);
            }

            return;
        }

        if (in_array($error, $this->ignoredErrors)) {
            $key = array_search($error, $this->ignoredErrors);
            unset($this->ignoredErrors[$key]);
        }
    }

    /**
     * Error handler method.
     *
     * @param int    $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int    $errLine
     * @param array  $errContext
     *
     * @return void
     */
    public function errorHandler(
        int $errNo,
        string $errStr,
        string $errFile = '',
        int $errLine = 0,
        array $errContext = []
    ) {
        $this->handlerType = self::HT_ERROR;
        $this->exception = new SpringyException($errStr, $errNo, $errFile, $errLine, $errContext);

        return $this->trigger();
    }

    /**
     * Exception handler method.
     *
     * @param Exception|Error|Throwable $err
     *
     * @return void
     */
    public function exceptionHandler($err)
    {
        $this->handlerType = self::HT_EXCEPTION;
        $this->exception = $err;

        return $this->trigger();
    }

    /**
     * Returns the list of ignored error codes.
     *
     * @return array
     */
    public function getIgnoredErrors(): array
    {
        return $this->ignoredErrors;
    }

    /**
     * Checks whether the error class or code is ignored by the handler.
     *
     * @param object|int|string $error
     *
     * @return bool
     */
    public function isIgnored($error): bool
    {
        if (is_object($error)) {
            return in_array(get_class($error), $this->ignoredErrors);
        }

        return in_array($error, $this->ignoredErrors);
    }

    /**
     * Sets error and exception handlers to own methods.
     *
     * @return void
     */
    public function setHandlers()
    {
        $this->prevErrorHandler = set_error_handler([$this, 'errorHandler']);
        $this->prevExceptionHandler = set_exception_handler([$this, 'exceptionHandler']);
    }

    /**
     * Triggers the error or exception.
     *
     * @return void
     */
    public function trigger()
    {
        if ($this->handlerType === null
            || $this->isIgnored($this->exception->getCode())
            || $this->isIgnored($this->exception)) {
            return;
        }

        $errCode = $this->exception->getCode();
        // Is a deprecated warning and is configured to ignore deprecations?
        if (in_array($errCode, [E_DEPRECATED, E_USER_DEPRECATED])) {
            return;
        }

        $this->restoreHandlers();

        if ($this->exception instanceof HttpError) {
            $this->httpError();

            return true;
        }

        // DB::rollBackAll();

        // Gets the error code.
        Response::getInstance()->header()->httpResponseCode(500);

        $this->displayError();

        return true;
    }
}
