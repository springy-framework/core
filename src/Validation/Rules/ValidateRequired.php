<?php

/**
 * Validation rule for checks required for data.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

/**
 * Validation rule for checks required for data.
 */
class ValidateRequired extends ValidationRule
{
    protected $required = true;

    /**
     * Validates the given value.
     *
     * Validates if the field is not null and not an empty string.
     *
     * @return bool
     */
    public function isValueValid(): bool
    {
        return ($this->value !== null) && ($this->value !== '');
    }
}
