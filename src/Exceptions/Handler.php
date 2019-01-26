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
        if ($this->prevErrorHandler) {
            restore_error_handler();
        }

        if ($this->prevExceptionHandler) {
            restore_exception_handler();
        }
    }

    /**
     * Returns the error name.
     *
     * @param int $errNo
     *
     * @return string
     */
    protected function errorName(int $errNo): string
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

        return $errorNames[$errNo] ?? 'Unknown Error ('.$errNo.')';
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
     * @param Exception $err
     *
     * @return void
     */
    public function exceptionHandler(Exception $err)
    {
        if (!($err instanceof Exception)) {
            return;
        }

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
     * Sets error and exception handlers to own methods.
     *
     * @return void
     */
    public function setHandlers()
    {
        $this->prevErrorHandler = set_error_handler([$this, 'errorHandler']);
        $this->prevExceptionHandler = set_exception_handler([$this, 'exceptionHandler']);
    }

    public function trigger()
    {
        if ($this->handlerType === null
            || in_array(
                $this->exception->getCode(),
                $this->ignoredErrors
            )) {
            return;
        }

        // DB::rollBackAll();

        // Gets the error code.
        $errCode = $this->exception->getCode();

        // Is a deprecated warning and is configured to ignore deprecations?
        if (in_array($errCode, [E_DEPRECATED, E_USER_DEPRECATED])) {
            return;
        }

        $errorName = $this->errorName($errCode);

        echo $errorName;
        exit(1);

        return true;
    }
}
