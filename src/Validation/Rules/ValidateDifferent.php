<?php

/**
 * Validation rule for checks data has different value.
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

/**
 * Validation rule for checks data has different value.
 */
class ValidateDifferent extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the value is different from.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        if (!isset($this->params[0])) {
            throw new BadMethodCallException(
                'Validation rule "Different" require comparison field name parameter no one given.'
            );
        }

        return $this->value !== ($this->input[$this->params[0]] ?? null);
    }
}
