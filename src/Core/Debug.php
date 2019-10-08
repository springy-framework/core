<?php

/**
 * Debug helper.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
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
    protected static $instance;

    /** @var array the debug informations array */
    protected $debug;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    private function __construct()
    {
        $this->debug = [];
        self::$instance = $this;
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
     * Formats a ver_dump to a beauty output.
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function dumpData($data): string
    {
        ob_start();
        var_dump($data);
        $xpto = ob_get_clean();
        $export = $xpto;
        $export = preg_replace('/\s*\bNULL\b/m', 'null', $export); // Cleanup NULL
        $export = preg_replace('/\s*\bbool\((true|false)\)/m', '$1', $export); // Cleanup booleans
        $export = preg_replace('/\s*\bint\((\d+)\)/m', '$1', $export); // Cleanup integers
        $export = preg_replace('/\s*\bfloat\(([\d.e-]+)\)/mi', '$1', $export); // Cleanup floats
        $export = preg_replace('/\s*\bstring\(\d+\) /m', '', $export); // Cleanup strings
        $export = preg_replace('/object\((\w+)\)(#\d+) (\(\d+\))/m', '$1', $export); // Cleanup objects definition
        //
        $export = preg_replace('/=>\s*/m', ' => ', $export); // No new line between array/object keys and properties
        $export = preg_replace('/\[([\w": ]+)\]/', '$1 ', $export); // remove square brackets in array/object keys
        // remove square brackets in array/object keys
        // $export = preg_replace('/\[([\w": ]+)\]/', ', $1 ', $export);
        // remove first coma in array/object properties listing
        // $export = preg_replace('/([{(]\s+), /', '$1  ', $export);
        $export = preg_replace('/\{\s+\}/m', '{}', $export);
        $export = preg_replace('/\s+$/m', '', $export); // Trim end spaces/new line

        $export = preg_replace('/(array\(\d+\) ){([^}]+)}/m', '$1[$2]', $export); // Cleanup objects definition
        $export = preg_replace('/(.+=>.+)/m', '$1,', $export); // Cleanup objects definition

        return $export;
    }

    /**
     * Formats a debug data to HTML output.
     *
     * @param array $debug
     *
     * @return string
     */
    protected function formatHtml(array $debug): string
    {
        return '<div class="springy-debug-info">'
            . '<div class="springy-debug-time"><strong>Time:</strong> '
            . sprintf('%.6f', $debug[1])
            . ' s | <strong>Memory:</strong> '
            . $this->getMemoryString($debug[0])
            . '  <a href="javascript:;" class="springy-debug-remove" title="Delete"></a></div>'
            . '<div class="springy-debug-value">'
            . $this->highligh($debug[2])
            . '</div>'
            . ($debug[4] > 0
                ? '<a class="spring-debug-backtrace-btn">Backtrace ('
                    . ($debug[4] > 0 ? 'last ' . $debug[4] : 'all')
                    . ') <i class="springy-arrow down"></i></a>'
                    . '<div class="spring-debug-backtrace-data">'
                    . $this->backtrace($debug[3])
                    . '</div>'
                : ''
            )
            . '</div>';
    }

    /**
     * Formats a debug data to JSON output.
     *
     * @param array $debug
     *
     * @return string
     */
    protected function formatJson(array $debug): string
    {
        $result = [
            'Time'   => sprintf('%.6f s', $debug[1]),
            'Memory' => $this->getMemoryString($debug[0]),
            'Debug'  => $this->dumpData($debug[2]),
        ];

        if ($debug[4] > 0) {
            $result['Backtrace'] = [
                'Quantity' => $debug[4] > 0 ? 'last ' . $debug[4] : 'all',
                'Backtrace' => $this->translateBacktrace($debug[3], true),
            ];
        }

        return json_encode($result);
    }

    /**
     * Formats a debug data to text plain output.
     *
     * @param array $debug
     *
     * @return string
     */
    protected function formatPlain(array $debug): string
    {
        return '> Time: ' . sprintf('%.6f s', $debug[1])
            . ' Memory: ' . $this->getMemoryString($debug[0]) . LF
            . '> ' . $this->highligh($debug[2]) . LF
            . ($debug[4] > 0
                ? '> Backtrace (' . ($debug[4] > 0 ? 'last ' . $debug[4] : 'all') . '):' . LF
                    . $this->backtrace($debug[3]) . LF . LF
                : ''
            );
    }

    /**
     * Gets a string of memory usage representation.
     *
     * @param int $memory
     *
     * @return string
     */
    protected function getMemoryString(int $memory): string
    {
        $unit = [
            'B',
            'KiB',
            'MiB',
            'GiB',
            'TiB',
            'PiB',
        ];

        return round(
            $memory / pow(1024, ($idx = floor(log($memory, 1024)))),
            2
        ) . ' ' . $unit[$idx];
    }

    /**
     * Translates the backtrace array to internal backtrace array.
     *
     * @param array $backtrace
     * @param bool  $clean
     *
     * @return array
     */
    protected function translateBacktrace(array $backtrace, bool $clean = false): array
    {
        $translated = [];

        foreach ($backtrace as &$value) {
            $file = $value['file'] ?? null;
            $line = $value['line'] ?? 1;

            $lines = $file
                ? (
                    $clean
                    ? file($file)
                    : explode(
                        '<br />',
                        str_replace(
                            '<br /></span>',
                            '</span><br />',
                            highlight_file($file, true)
                        )
                    )
                ) : ['unknown file'];

            $translated[] = [
                'file'    => $file,
                'line'    => $line,
                'args'    => $value['args'] ?? [],
                'content' => trim(preg_replace('/^(&nbsp;)+/', '', $lines[$line - 1])),
            ];

            // Releasing memory
            $lines = null;
            $value = null;
        }

        return $translated;
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
    ) {
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
     * Parses the backtrace to HTML string.
     *
     * @param array $debug
     *
     * @return string
     */
    public function backtrace(array $backtrace = []): string
    {
        if (empty($backtrace)) {
            return '';
        }

        $result = '<ul>';
        $translated = $this->translateBacktrace($backtrace);

        // Build the backtrace HTML
        foreach ($translated as $trace) {
            $trace['content'] = preg_replace('/^<\/span>/', '', trim($trace['content']));
            if (!preg_match('/<\/span>$/', $trace['content'])) {
                $trace['content'] .= '</span>';
            }

            $line = sprintf('[%05d]', $trace['line']);
            $result .= '<li><p><strong>' . $line . '</strong> '
                . $trace['file'] . '</p><div class="springy-debug-backtrace-content">'
                . $trace['content'] . '</div>';

            if (count($trace['args'])) {
                $result .= '<ul class="springy-debug-backtrace-args">';

                foreach ($trace['args'] as $arg) {
                    $result .= '<li>' . $this->highligh($arg) . '</li>';
                }

                $result .= '</ul>';
            }

            $result .= '</li>';
        }

        return $result . '</ul>';
    }

    /**
     * Gets the debug text.
     *
     * @return string
     */
    public function get(string $format = 'html'): string
    {
        $format = 'format' . ucfirst($format);
        if (!method_exists($this, $format)) {
            return '';
        }

        $return = '';
        foreach ($this->debug as $debug) {
            $return .= $this->$format($debug);
        }

        return $return;
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
     * Hightlights the data details.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function highligh($data): string
    {
        $export = $this->dumpData($data);

        if (php_sapi_name() === 'cli') {
            return $export;
        }

        return str_replace(
            '&lt;?php&nbsp;',
            '',
            str_replace(
                '&nbsp;?&gt;',
                '',
                highlight_string('<?php ' . $export, true)
            )
        );
    }

    /**
     * Injects the debug data into HTML page.
     *
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     *
     * @param string $content
     *
     * @return void
     */
    public function inject(string $content)
    {
        $this->add(
            'Execution time: '
            . sprintf('%.8f', Kernel::getInstance()->runTime())
            . ' seconds ' . LF
            . 'Maximum memory used: ' . $this->getMemoryString(memory_get_peak_usage(true)),
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
        if (self::$instance === null) {
            new self();
        }

        return self::$instance;
    }
}
