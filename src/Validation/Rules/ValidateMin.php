<?php

/**
 * Validation rule for checks minimum value for data.
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
 * Validation rule for checks minimum value for data.
 */
class ValidateMin extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the value meets the minimum required.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        if (!isset($this->params[0])) {
            throw new BadMethodCallException(
                'Validation rule "' . get_class($this)
                . '" requires the minimum value parameter no one given.'
            );
        }

        return $this->value >= $this->params[0];
    }
}
