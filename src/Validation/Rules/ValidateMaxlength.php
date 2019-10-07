<?php

/**
 * Validation rule for checks maximum length for data.
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
 * Validation rule for checks maximum length for data.
 */
class ValidateMaxlength extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if text matches the longest length allowed.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        if (!isset($this->params[0])) {
            throw new BadMethodCallException(
                'Validation rule "' . get_class($this)
                . '" requires the maximum value parameter no one given.'
            );
        }

        return mb_strlen($this->value, $this->charset) <= $this->params[0];
    }
}