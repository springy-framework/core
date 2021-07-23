<?php

/**
 * Debug plain text output formater.
 *
 * @copyright 2021 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Core\DebugFormat;

/**
 * Plain class.
 */
class Plain
{
    /** @var string */
    protected $data;

    public function __construct()
    {
        $this->data = '';
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
     * Parses the backtrace to HTML string.
     *
     * @param array $debug
     *
     * @return string
     */
    protected function backtrace(array $backtrace = []): string
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
     * Formats a debug data to text plain output.
     *
     * @param array $debug
     *
     * @return string
     */
    protected function format(array $debug): string
    {
        return '> Time: ' . sprintf('%.6f s', $debug[1])
            . ' Memory: ' . memory_string($debug[0]) . LF
            . '> ' . $this->highligh($debug[2]) . LF
            . (
                $debug[4] > 0
                ? '> Backtrace (' . ($debug[4] > 0 ? 'last ' . $debug[4] : 'all') . '):' . LF
                    . $this->backtrace($debug[3]) . LF . LF
                : ''
            );
    }

    /**
     * Hightlights the data details.
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function highligh($data): string
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
     * Adds debug data.
     *
     * @param array $debug
     *
     * @return void
     */
    public function add(array $debug): void
    {
        $this->data .= $this->format($debug);
    }

    /**
     * Gets debug formated data.
     *
     * @return string
     */
    public function get(): string
    {
        return $this->data;
    }
}
