<?php

/**
 * Database lost connection detector trait.
 *
 * Inspired in Laravel's Illuminate\Database\DetectsLostConnections class.
 *
 * @version 1.0.0
 */

namespace Springy\Database;

use Throwable;

/**
 * Database lost connection detector trait.
 */
trait LostConnectionDetector
{
    /**
     * Determine if the given exception was caused by a lost connection.
     *
     * @param Throwable $err
     *
     * @return bool
     */
    protected function isLostConnection(Throwable $err): bool
    {
        return in_array($err->getMessage(), [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
            'Physical connection is not usable',
            'TCP Provider: Error code 0x68',
            'ORA-03114',
            'Packets out of order. Expected',
            'Adaptive Server connection failed',
            'Communication link failure',
        ]);
    }
}
