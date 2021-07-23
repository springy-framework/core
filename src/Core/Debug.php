<?php

/**
 * Debug helper.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   2.0.0
 */

namespace Springy\Core;

use Springy\HTTP\Response;

/**
 * Debug helper.
 */
class Debug
{
    /** @var self globally singleton instance */
    private static $instance;

    /** @var array the debug informations array */
    private $debug;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    final private function __construct()
    {
        $this->debug = [];
    }

    /**
     * Prevents the instance from being cloned (which would create a second instance of it).
     */
    private function __clone()
    {
    }

    /**
     * Prevents from being unserialized (which would create a second instance of it).
     *
     * @SuppressWarnings(UnusedPrivateMethod)
     */
    private function __wakeup()
    {
    }

    /**
     * Adds an information to the debug collection.
     *
     * @param mixed $data
     * @param bool  $revert
     * @param bool  $saveBacktrace
     * @param int   $backtraceLimit
     * @param int   $jumptrace
     *
     * @return void
     */
    public function add(
        $data,
        bool $revert = true,
        bool $saveBacktrace = true,
        int $backtraceLimit = 3,
        int $jumptrace = 0
    ): void {
        $backtrace = [];
        if ($saveBacktrace) {
            $backtrace = debug_backtrace(
                DEBUG_BACKTRACE_PROVIDE_OBJECT,
                $backtraceLimit ? $backtraceLimit + 1 + ($jumptrace > 0 ? $jumptrace : 0) : $backtraceLimit
            );
            array_shift($backtrace);
            while ($jumptrace) {
                array_shift($backtrace);
                $jumptrace -= 1;
            }
        }

        $debug = [
            memory_get_usage(true),
            Kernel::getInstance()->runTime(),
            $data,
            $backtrace,
            $saveBacktrace ? $backtraceLimit : -1,
        ];

        if ($revert) {
            array_unshift($this->debug, $debug);

            return;
        }

        $this->debug[] = $debug;
    }

    /**
     * Gets the debug text.
     *
     * @return string
     */
    public function get(string $format = 'html'): string
    {
        $className = 'Springy\\Core\\DebugFormat\\' . ucfirst($format);
        if (!class_exists($className)) {
            return '';
        }

        /** @var Springy\Core\DebugFormat\Plain */
        $formater = new $className();
        foreach ($this->debug as $debug) {
            $formater->add($debug);
        }

        return $formater->get();
    }

    /**
     * Gets the debug data as a reduced array.
     *
     * @return array
     */
    public function getSimpleData(): array
    {
        $debug = [];
        foreach ($this->debug as $data) {
            $debug[] = [
                'memory' => $data[0],
                'time'   => $data[1],
                'data'   => $data[2],
            ];
        }

        return $debug;
    }

    /**
     * Injects the debug data into HTML page.
     *
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     *
     * @param string $content
     *
     * @return string
     */
    public function inject(string $content): string
    {
        $this->add(
            'Execution time: '
            . sprintf('%.8f', Kernel::getInstance()->runTime())
            . ' seconds ' . LF
            . 'Maximum memory used: ' . memory_string(memory_get_peak_usage(true)),
            true,
            false
        );

        // Gets the Content-Type header
        $cType = Response::getInstance()->header()->getContentType();

        // Injects into a JSON
        if ($cType == 'application/json') {
            $json = json_decode($content);
            if ($json === false || strlen($content) < 2 || substr($content, -1, 1) !== '}') {
                return $content;
            }

            return substr_replace($content, ',"springy_debug":' . $this->get('json'), -1, 0);
        }

        // Others content types than HTML
        if ($cType != 'text/html') {
            return $content;
        }

        // Injects into a HTML
        $htmlDebug = '';
        $debugTemplate = __DIR__ . DS . 'assets' . DS . 'debug.html';
        if (file_exists($debugTemplate) && $htmlDebug = file_get_contents($debugTemplate)) {
            $htmlDebug = preg_replace(
                [
                    '/<!-- DEBUG CONTENT \(.+\) -->/mu',
                    '~<!--.*?-->~s',
                    '!/\*.*?\*/!s',
                    "/\n\s+/",
                    "/\n(\s*\n)+/",
                    "!\n//.*?\n!s",
                    "/\n\}(.+?)\n/",
                    "/\}\s+/",
                    "/,\n/",
                    "/>\n/",
                    "/\{\s*?\n/",
                    "/\}\n/",
                    "/;\n/",
                ],
                [
                    $this->get(),
                    '',
                    '',
                    LF,
                    LF,
                    LF,
                    "}\\1\n",
                    '}',
                    ', ',
                    '>',
                    '{',
                    '} ',
                    ';',
                ],
                $htmlDebug
            );
        }

        if (preg_match('/<\/body>/', $content)) {
            return preg_replace('/<\/body>/', $htmlDebug . '</body>', $content);
        }

        return preg_replace('/^(.*?)$/', $htmlDebug . '\\1', $content);
    }

    /**
     * Returns current instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
