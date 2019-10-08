<?php

/**
 * Validation rule for checks email data.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

use Springy\Utils\StringUtils;

class ValidateEmail extends ValidationRule
{
    use StringUtils;

    /**
     * Validates the given value.
     *
     * Validates whether the value is an email address.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return $this->isValidEmailAddress($this->value);
    }
}
