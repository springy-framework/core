<?php

/**
 * Rule for validations of user-assigned data.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation;

use BadMethodCallException;
use Springy\Utils\StringUtils;

/**
 * Rule class for validations of user-assigned data.
 */
class Rule
{
    use StringUtils;

    /** @var Springy\Validation\Rules\ValidationRule the validation object */
    protected $method;

    /**
     * Constructor.
     *
     * @param string $field
     * @param string $rule
     *
     * The $rule can be a string in the format 'ruleName:param1,param2[,paramN]'
     */
    public function __construct(&$input, string $field, string $rule, array $params = [], string $message = null)
    {
        $rule = ucwords(strtolower(str_replace('_', '', $rule)));
        $class = __NAMESPACE__ . '\\Rules\\Validate' . $rule;

        if (!class_exists($class)) {
            throw new BadMethodCallException(
                'Validation rule "' . $rule
                . '" has no equivalent method for validation.'
            );
        }

        $this->method = new $class($input, $field, $params, $message);
    }

    /**
     * Returns the error message.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->method->getErrorMessage();
    }

    /**
     * Returns the result of the validation proccess.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->method->isValid();
    }

    /**
     * Validates the value into the rule.
     *
     * @param array $fields
     *
     * @return bool
     */
    public function validate(): self
    {
        $this->method->validate();

        return $this;
    }
}
