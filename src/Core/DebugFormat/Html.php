<?php

/**
 * Debug HTML output formater.
 *
 * @copyright 2021 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Core\DebugFormat;

/**
 * Html class.
 */
class Html extends Plain
{
    /**
     * Formats a debug data to HTML output.
     *
     * @param array $debug
     *
     * @return string
     */
    protected function format(array $debug): string
    {
        $btcount = $debug[4] > 0 ? 'last ' . $debug[4] : 'all';

        return '<div class="springy-debug-info">'
            . '<div class="springy-debug-time"><strong>Time:</strong> '
            . sprintf('%.6f', $debug[1])
            . ' s | <strong>Memory:</strong> '
            . memory_string($debug[0])
            . '  <a href="javascript:;" class="springy-debug-remove" title="Delete"></a></div>'
            . '<div class="springy-debug-value">'
            . $this->highligh($debug[2])
            . '</div>'
            . (
                $debug[4] > 0
                ? '<a class="spring-debug-backtrace-btn">Backtrace ('
                    . $btcount
                    . ') <i class="springy-arrow down"></i></a>'
                    . '<div class="spring-debug-backtrace-data">'
                    . $this->backtrace($debug[3])
                    . '</div>'
                : ''
            )
            . '</div>';
    }
}
