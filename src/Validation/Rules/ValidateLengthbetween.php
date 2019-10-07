<?php

/**
 * Validation rule for checks if data length is between a range.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

use BadMethodCallException;

/**
 * Validation rule for checks if data length is between a range.
 */
class ValidateLengthbetween extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the text length is within the allowed range.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        if (count($this->params) < 2) {
            throw new BadMethodCallException(
                'Validation rule "' . get_class($this)
                . '" require 2 parameters ' . count($this->params) . ' given.'
            );
        }

        $length = mb_strlen($this->value, $this->charset);

        return ($length >= $this->params[0]) && ($length <= $this->params[1]);
    }
}