<?php

/**
 * Validation rule for checks maximum value for data.
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
 * Validation rule for checks maximum value for data.
 */
class ValidateMax extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the value meets the maximum allowed.
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

        return $this->value <= $this->params[0];
    }
}