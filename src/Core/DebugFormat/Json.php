<?php

/**
 * Debug Json output formater.
 *
 * @copyright 2021 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Core\DebugFormat;

/**
 * Json class.
 */
class Json extends Plain
{
    /**
     * Formats a debug data to JSON output.
     *
     * @param array $debug
     *
     * @return string
     */
    protected function format(array $debug): string
    {
        $result = [
            'Time'   => sprintf('%.6f s', $debug[1]),
            'Memory' => memory_string($debug[0]),
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
     * Adds debug data.
     *
     * @param array $debug
     *
     * @return void
     */
    public function add(array $debug): void
    {
        $this->data .= (empty($this->data) ? '' : ',') . $this->format($debug);
    }

    /**
     * Gets debug formated data.
     *
     * @return string
     */
    public function get(): string
    {
        return json_decode('[' . json_encode($this->data) . ']');
    }
}
