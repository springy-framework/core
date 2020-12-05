<?php

/**
 * Validation rule for checks data between a range.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

use BadMethodCallException;

class ValidateBetween extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the value is between the minimum and maximum range.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        if (count($this->params) < 2) {
            throw new BadMethodCallException(
                'Validation rule "Between" require at least 2 parameters '
                . count($this->params) . ' given.'
            );
        }

        return ($this->value >= $this->params[0]) && ($this->value <= $this->params[1]);
    }
}
