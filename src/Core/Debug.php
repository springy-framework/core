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
     * @param bool  $highlight
     * @param bool  $revert
     * @param bool  $saveBacktrace
     * @param intr  $backtraceLimit
     *
     * @return void
     */
    public function add(
        $data,
        bool $highlight = true,
        bool $revert = true,
        bool $saveBacktrace = true,
        int $backtraceLimit = 3
    ) {
        $backtrace = [];
        if ($saveBacktrace) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $backtraceLimit);
            array_shift($backtrace);
        }

        $debug = [
            memory_get_usage(true),
            $highlight,
            $data,
            $backtrace,
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
     * @return string
     */
    protected function outputFormat(): string
    {
        if (self::$cliOutput) {
            return '- Alocated memory: %s'.LF.
                '- >>> %s'.LF.
                '- Backtrace:'.
                '%s'.LF.LF;
        }

        return '<div class="spring-debug-info">'.
            '<p>Allocated memory: %s</p>'.
            '<div>%s</div>'.
            '<div class="spring-debug-backtrace-title">Debug backtrace</div>'.
            '<div class="spring-debug-backtrace-data">%s</div>'.
            '</div>';
    }

    public function backtrace(array $debug = [], int $limit = 10)
    {
        $aDados = [];

        foreach ($debug as $value) {
            if (empty($value['line']) || strpos($value['file'], 'Errors.php') > 0) {
                continue;
            }

            $linhas = explode('<br />', str_replace('<br /></span>', '</span><br />', highlight_string(file_get_contents($value['file']), true)));
            $aDados[] = [
                'file'    => $value['file'],
                'line'    => $value['line'],
                'args'    => isset($value['args']) ? $value['args'] : [],
                'content' => trim(preg_replace('/^(&nbsp;)+/', '', $linhas[$value['line'] - 1])),
            ];
        }

        $result = '<ul style="font-family:Arial, Helvetica, sans-serif; font-size:12px">';
        $htmlLI = 0;

        foreach ($aDados as $backtrace) {
            if ($backtrace['line'] > 0) {
                $backtrace['content'] = preg_replace('/^<\/span>/', '', trim($backtrace['content']));
                if (!preg_match('/<\/span>$/', $backtrace['content'])) {
                    $backtrace['content'] .= '</span>';
                }

                $line = sprintf('[%05d]', $backtrace['line']);
                $result .= '<li style="margin-bottom: 5px; '.($htmlLI + 1 < count($aDados) ? 'border-bottom:1px dotted #000; padding-bottom:5px' : '').'"><span><b>'.$line.'</b>&nbsp;<b>'.$backtrace['file'].'</b></span><br />'.$backtrace['content'];

                if (count($backtrace['args'])) {
                    $result .= is_array($backtrace['args'])
                        ? '<div>'.$this->highligh($backtrace['args']).'</div>'
                        : $backtrace['args'];
                }

                $result .= '</li>';
                $htmlLI++;
            }
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
        $format = $this->outputFormat();
        $return = [];

        foreach (self::$debug as $debug) {
            $unit = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
            $memory = round($debug[0] / pow(1024, ($idx = floor(log($debug[0], 1024)))), 2).' '.$unit[$idx];

            $return[] = sprintf(
                $format,
                $memory,
                $debug[1] ? $this->highligh($debug[2]) : $debug[2],
                $this->backtrace($debug[3])
            );
        }

        return implode(self::$cliOutput ? LF : '<hr />', $return);
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
        if (self::$cliOutput) {
            return print_r($data, true);
        }

        if (is_object($data)) {
            if (method_exists($data, '__toString')) {
                return str_replace(
                    '&lt;?php&nbsp;',
                    '',
                    str_replace(
                        '&nbsp;?&gt;',
                        '',
                        highlight_string(
                            '<?php '.var_export($data->__toString(), true),
                            true
                        )
                    )
                );
            }

            return '<pre>'.print_r($data, true).'</pre>';
        }

        return str_replace(
            '&lt;?php&nbsp;',
            '',
            str_replace(
                '&nbsp;?&gt;',
                '',
                highlight_string('<?php '.print_r($data, true), true)
            )
        );
    }

    public function inject(string $content)
    {
        // if (self::$cliOutput) {
        //     return $content;
        // }

        $size = memory_get_peak_usage(true);
        $unit = ['b', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $memory = round($size / pow(1024, ($idx = floor(log($size, 1024)))), 2).' '.$unit[$idx];
        unset($unit, $size);

        $this->add('Runtime execution time: '.
            Kernel::getInstance()->runTime().
            ' seconds'.LF.
            'Maximum memory consumption: '.$memory,
            true, false, false
        );
        unset($memory);

        $htmlDebug = '';
        $debugTemplate = __DIR__.DS.'view'.DS.'debug.html';
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
                    "/;\n/"
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
                    ';'
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
