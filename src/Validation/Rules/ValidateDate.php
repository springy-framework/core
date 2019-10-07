<?php

/**
 * Validation rule for checks date data.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

/**
 * Validation rule for checks date data.
 */
class ValidateDate extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the value is a valid date.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        $date = date_parse($this->value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }
}