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

class Debug
{
    /** @var self globally singleton instance */
    protected static $instance;

    /** @var bool the output must be in CLI format */
    protected static $cliOutput;
    /** @var array the debug informations array */
    protected static $debug;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (self::$instance !== null) {
            return;
        }

        self::$cliOutput = Kernel::getInstance()->environmentType() === Kernel::ENV_TYPE_CLI;
        self::$debug = [];
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
            array_unshift(self::$debug, $debug);

            return;
        }

        self::$debug[] = $debug;
    }

    /**
     * Gets the string model for each debug information.
     *
     * @param int $backtrace
     *
     * @return string
     */
    protected function outputFormat(int $backtrace = 0): string
    {
        if (self::$cliOutput) {
            return '> Time: %.6d  Memory: %s'.LF
                .'> %s'.LF
                .($backtrace
                    ? '> Backtrace (%s):'.LF.'%s'.LF.LF
                    : ''
                );
        }

        return '<div class="springy-debug-info"><div class="springy-debug-time"><strong>Time:</strong>'
            .' %.6f s | <strong>Memory:</strong> %s <a href="javascript:;" class="springy-debug-remove" title="Delete"></a></div>'
            .'<div class="springy-debug-value">%s</div>'
            .($backtrace > 0
                ? '<a class="spring-debug-backtrace-btn">Backtrace (%s) <i class="springy-arrow down"></i></a>'
                    .'<div class="spring-debug-backtrace-data">%s</div>'
                : ''
            )
            .'</div>';
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
        $translated = [];

        // Translates the backtrace array to internal backtrace array
        foreach ($backtrace as &$value) {
            $file = $value['file'] ?? null;
            $line = $value['line'] ?? 1;

            $lines = $file ? explode(
                '<br />',
                str_replace(
                    '<br /></span>',
                    '</span><br />',
                    highlight_file($file, true)
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

        // Build the backtrace HTML
        foreach ($translated as $trace) {
            $trace['content'] = preg_replace('/^<\/span>/', '', trim($trace['content']));
            if (!preg_match('/<\/span>$/', $trace['content'])) {
                $trace['content'] .= '</span>';
            }

            $line = sprintf('[%05d]', $trace['line']);
            $result .= '<li><p><strong>'.$line.'</strong> '
                .$trace['file'].'</p><div class="springy-debug-backtrace-content">'
                .$trace['content'].'</div>';

            if (count($trace['args'])) {
                $result .= '<ul class="springy-debug-backtrace-args">';

                foreach ($trace['args'] as $arg) {
                    $result .= '<li>'.$this->highligh($arg).'</li>';
                }

                $result .= '</ul>';
            }

            $result .= '</li>';
        }

        return $result.'</ul>';
    }

    /**
     * Gets the debug text.
     *
     * @return string
     */
    public function get(): string
    {
        $return = [];

        foreach (self::$debug as $debug) {
            $unit = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
            $memory = round($debug[0] / pow(1024, ($idx = floor(log($debug[0], 1024)))), 2).' '.$unit[$idx];

            $return[] = sprintf(
                $this->outputFormat($debug[4]),
                $debug[1],
                $memory,
                $this->highligh($debug[2]),
                $debug[4] > 0 ? 'last '.$debug[4] : 'all',
                $this->backtrace($debug[3])
            );
        }

        return implode(self::$cliOutput ? LF : '', $return);
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
        // //@todo array cleaning
        $export = preg_replace('/=>\s*/m', ' => ', $export); // No new line between array/object keys and properties
        $export = preg_replace('/\[([\w": ]+)\]/', '$1 ', $export); // remove square brackets in array/object keys
        // $export = preg_replace('/\[([\w": ]+)\]/', ', $1 ', $export); // remove square brackets in array/object keys
        // $export = preg_replace('/([{(]\s+), /', '$1  ', $export); // remove first coma in array/object properties listing
        $export = preg_replace('/\{\s+\}/m', '{}', $export);
        $export = preg_replace('/\s+$/m', '', $export); // Trim end spaces/new line

        $export = preg_replace('/(array\(\d+\) ){([^}]+)}/m', '$1[$2]', $export); // Cleanup objects definition
        $export = preg_replace('/(.+=>.+)/m', '$1,', $export); // Cleanup objects definition

        if (self::$cliOutput) {
            return $export;
        }

        return str_replace(
            '&lt;?php&nbsp;',
            '',
            str_replace(
                '&nbsp;?&gt;',
                '',
                highlight_string('<?php '.$export, true)
            )
        );
    }

    /**
     * Injects the debug data into HTML page.
     *
     * @param string $content
     *
     * @return void
     */
    public function inject(string $content)
    {
        if (self::$cliOutput) {
            return $content;
        }

        $size = memory_get_peak_usage(true);
        $unit = ['b', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $memory = round($size / pow(1024, ($idx = floor(log($size, 1024)))), 2).' '.$unit[$idx];
        unset($unit, $size);

        $this->add('Execution time: '.
            sprintf('%.8f', Kernel::getInstance()->runTime()).
            ' seconds'.LF.'Maximum memory used: '.$memory,
            true, false
        );
        unset($memory);

        $htmlDebug = '';
        $debugTemplate = __DIR__.DS.'assets'.DS.'debug.html';
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
                ], [
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
                ], $htmlDebug
            );
        }

        if (preg_match('/<\/body>/', $content)) {
            return preg_replace('/<\/body>/', $htmlDebug.'</body>', $content);
        }

        return preg_replace('/^(.*?)$/', $htmlDebug.'\\1', $content);
    }

    /**
     * Returns current instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
