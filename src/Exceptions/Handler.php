<?php
/**
 * Errors handler class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

use DateTime;
use PDOException;
use Springy\Core\Configuration;
use Springy\Core\Debug;
use Springy\HTTP\Response;
use Springy\Mail\Mailer;
use Springy\Template\Template;
use Springy\Utils\NetworkUtils;
use Springy\Utils\StringUtils;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class Handler
{
    use NetworkUtils;
    use StringUtils;

    const HT_ERROR = 1;
    const HT_EXCEPTION = 2;

    /** @var mixed previous error handler */
    protected $prevErrorHandler;
    /** @var mixed previous exception handler */
    protected $prevExceptionHandler;

    /** @var \Throwable last exception throwed */
    protected $exception;
    /** @var int type of the last handler throwed */
    protected $handlerType;
    /** @var array list of ignored errors */
    protected $ignoredErrors;
    /** @var string directory to save error logs */
    protected $logDir;
    /** @var array the list of errors that should not be logged nor reported */
    protected $unreportable;
    /** @var array the webmasters email addresses */
    protected $webmasters;

    /**
     * Constructor.
     */
    public function __construct(string $logDir = '', array $unreportable = [])
    {
        error_reporting(E_ALL);
        $this->logDir = $logDir;
        $this->ignoredErrors = [];
        $this->unreportable = $unreportable;
        $this->webmasters = [];
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
     * Displays the application error trace.
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     *
     * @return void
     */
    protected function displayCliError()
    {
        echo LF.'Application error:'.LF;
        echo '  '.$this->exception->getMessage().LF;
        echo LF.'Stack trace:'.LF;

        foreach ($this->exception->getTrace() as $index => $trace) {
            echo '  '.str_pad($index, 3, ' ', STR_PAD_LEFT).': ';

            if (!isset($trace['file'])) {
                echo $trace['class'] ?? '';
                echo $trace['type'] ?? '';
                echo $trace['function'] ?? '';
                echo LF;

                continue;
            }

            echo $trace['file'].': '.$trace['line'].LF;
        }
        // echo $this->exception->getTraceAsString();

        exit(1);
    }

    /**
     * Displays the application error page.
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     *
     * @param mixed $errCode
     * @param mixed $responseCode
     *
     * @return void
     */
    protected function displayError($errCode, $responseCode)
    {
        $this->save2Log();

        if (php_sapi_name() === 'cli') {
            $this->displayCliError();
        }

        $response = Response::getInstance();
        $response->header()->clear();
        $response->header()->httpResponseCode($responseCode);
        $response->header()->contentType('text/html', 'UTF-8', true);
        $response->header()->pragma('no-cache');
        $response->header()->expires('0');
        $response->header()->cacheControl('must-revalidate, post-check=0, pre-check=0');
        $response->header()->cacheControl('private', false);
        $response->body($this->getErrorView($errCode, $responseCode));
        $response->send();

        exit(1);
    }

    /**
     * Returns the CRC for current error.
     *
     * @return string
     */
    protected function getCrc(): string
    {
        return hash('crc32', $this->exception->getCode().$this->exception->getFile().$this->exception->getLine());
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
     * Returns the HTML error content.
     *
     * @param mixed $errCode
     * @param mixed $responseCode
     *
     * @return string
     */
    protected function getErrorView($errCode, $responseCode): string
    {
        $config = Configuration::getInstance();
        $sufix = $config->get('template.file_sufix');
        $path = $config->get('template.paths.errors').DS.'http'.$responseCode.'error'.$sufix;
        if (is_file($path)) {
            $template = new Template();
            $template->setTemplateDir($config->get('template.paths.errors'));
            $template->setTemplate('http'.$responseCode.'error');
            $template->assign('errorCode', $errCode);
            $template->assign('responseCode', $responseCode);
            $template->assign('exception', $this->exception);

            return $template->fetch();
        }

        $path = __DIR__.DS.'assets'.DS.'http'.$responseCode.'error.html';
        if (is_file($path)) {
            return file_get_contents($path);
        }

        return $this->getErrorName($errCode)
            .' - '.$this->exception->getMessage()
            .' on ['.$this->exception->getLine().'] '
            .$this->exception->getFile();
    }

    /**
     * Gets the error from Yaml file or prepare new array.
     *
     * @param string $filePath
     *
     * @return array
     */
    protected function getYaml(string $filePath): array
    {
        if (realpath($filePath)) {
            return Yaml::parseFile($filePath);
        }

        $remoteAddr = $this->getRealRemoteAddr();

        $error = [
            'crc'          => $this->getCrc(),
            'occurrences'  => 0,
            'date'         => (new DateTime())->format('c'),
            'informations' => [
                'code'        => $this->exception->getCode(),
                'file'        => $this->exception->getFile(),
                'line'        => $this->exception->getLine(),
                'message'     => $this->exception->getMessage(),
                'first'       => (new DateTime())->format('c'),
                'uname'       => php_uname(),
                'safe_mode'   => ini_get('safe_mode') ? 'Yes' : 'No',
                'sapi_name'   => php_sapi_name(),
            ],
            'request' => [
                'host'     => $_SERVER['HTTP_HOST'] ?? '',
                'uri'      => $_SERVER['REQUEST_URI'] ?? '',
                'method'   => $_SERVER['REQUEST_METHOD'] ?? '',
                'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? '',
                'secure'   => $_SERVER['HTTPS'] ?? '',
            ],
            'client' => [
                'address'    => $remoteAddr,
                'referrer'   => $_SERVER['HTTP_REFERER'] ?? '',
                'reverse'    => $remoteAddr ? gethostbyaddr($remoteAddr) : '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ],
            'php_vars' => [
                'cookie'  => $_COOKIE ?? [],
                'env'     => $_ENV ?? [],
                'files'   => $_FILES ?? [],
                'get'     => $_GET ?? [],
                'post'    => $_POST ?? [],
                'server'  => $_SERVER ?? [],
                'session' => $_SESSION ?? [],
            ],
            'debug' => Debug::getInstance()->getSimpleData(),
            'trace' => $this->exception->getTrace(),
        ];

        return $error;
    }

    /**
     * Sends error report to webmasters.
     *
     * @param string $file
     *
     * @return void
     */
    protected function reportWebmaster(string $file)
    {
        if (!count($this->webmasters)) {
            return;
        }

        try {
            $email = new Mailer();
        } catch (Throwable $err) {
            // Discards the error and don't send the report.
            $err = null;

            return;
        }

        $message = sprintf(
            '<strong>%s - System Error Report</strong><br><br>'
            .'The application was aborted with the following error:<br><br>'
            .'<p>Error code: <strong>%s</strong></p>'
            .'<p>Error Message: <font color="red">%s</font></p>'
            .'<p>File: <strong>%s</strong></p>'
            .'<p>Line: <strong>%d</strong></p><br>'
            .'The error was identified with the CRC <font color="red">%s</font>',
            app_name(),
            $this->exception->getCode(),
            $this->exception->getMessage(),
            $this->exception->getFile(),
            $this->exception->getLine(),
            $this->getCrc()
        );

        foreach ($this->webmasters as $address) {
            $email->addTo($address, 'Webmaster');
        }

        $email->setFrom($this->webmasters[0], app_name().' - System Error Report');
        $email->setSubject(sprintf(
            'Error on %s v%s [%s] at %s',
            app_name(),
            app_version(),
            app_env(),
            $_SERVER['HTTP_HOST'] ?? $_SERVER['PHP_SELF'] ?? '?'
        ));
        $email->setBody($message);
        if (is_file($file)) {
            $email->addAttachment($file, 'errorlog', 'text/plain');
        }
        $email->send();
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
     * Saves the error/exception to Yml log file.
     *
     * @return void
     */
    protected function save2Log()
    {
        if (!$this->logDir || !is_dir($this->logDir) || $this->shouldntReport()) {
            return;
        }

        $errorCode = $this->getCrc();
        $filePath = $this->logDir.DS.$errorCode.'.yml';
        $error = $this->getYaml($filePath);
        $error['occurrences'] += 1;
        $error['date'] = (new DateTime())->format('c');

        $yaml = Yaml::dump($error);

        $shouldntReport = is_file($filePath);

        file_put_contents($filePath, $yaml);

        if ($shouldntReport) {
            return;
        }

        $this->reportWebmaster($filePath);
    }

    /**
     * Checks whether the error/exception should not be reported.
     *
     * @return bool
     */
    protected function shouldntReport()
    {
        return in_array(get_class($this->exception), $this->unreportable) ||
            in_array($this->exception->getCode(), $this->unreportable);
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
     * Adds an email to the webmasters list.
     *
     * @param string $email
     *
     * @return void
     */
    public function addWebmaster(string $email)
    {
        if (in_array($email, $this->webmasters)) {
            return;
        }

        if (!$this->isValidEmailAddress($email, false)) {
            return;
        }

        $this->webmasters[] = $email;
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
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     *
     * @return void
     */
    public function errorHandler(
        int $errno,
        string $errstr,
        string $errfile = '',
        int $errline = 0
    ) {
        $this->handlerType = self::HT_ERROR;
        $this->exception = new SpringyException($errstr, $errno, $this->exception, $errfile, $errline);

        return $this->trigger();
    }

    /**
     * Exception handler method.
     *
     * @param \Exception|\Error|\Throwable $err
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
     * Sets the output log folder path.
     *
     * @param string $logDir
     *
     * @return void
     */
    public function setLogDir(string $logDir)
    {
        $this->logDir = $logDir;
    }

    /**
     * Sets the list of errors that should not be logged nor reported.
     *
     * @param array $unreportable
     *
     * @return void
     */
    public function setUnreportable(array $unreportable)
    {
        $this->unreportable = $unreportable;
    }

    /**
     * Sets the webmasters array.
     *
     * @param string|array $webmasters
     *
     * @return void
     */
    public function setWebmasters($webmasters)
    {
        $this->webmasters = [];

        if (is_array($webmasters)) {
            foreach ($webmasters as $email) {
                $this->addWebmaster($email);
            }

            return;
        }

        $this->addWebmaster($webmasters);
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

        $responseCode = 500;
        $errCode = $this->exception->getCode();
        // Is a deprecated warning and is configured to ignore deprecations?
        if (in_array($errCode, [E_DEPRECATED, E_USER_DEPRECATED])) {
            return;
        }

        $this->restoreHandlers();

        if ($this->exception instanceof HttpError) {
            $responseCode = $this->exception->getStatusCode();
            $errCode = $this->exception->getStatusCode();
        }

        $this->displayError($errCode, $responseCode);

        return true;
    }
}
