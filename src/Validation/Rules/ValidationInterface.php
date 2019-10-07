<?php

/**
 * Interface for validation rules validator.
 *
 * This driver is an interface to building rule validators.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Validation\Rules;

/**
 * Interface for validation rules validator.
 */
interface ValidationInterface
{
    public function isValueValid(): bool;
}