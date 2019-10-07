<?php

/**
 * Validation rule for checks when data has same value of another.
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
 * Validation rule for checks when data has same value of another.
 */
class ValidateSame extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates whether the value is the same as that of another field.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        if (!isset($this->params[0])) {
            throw new BadMethodCallException(
                'Validation rule "' . get_class($this)
                . '" require comparison field name parameter no one given.'
            );
        }

        $other = $this->input[$this->params[0]] ?? null;

        return $this->value === $other;
    }
}