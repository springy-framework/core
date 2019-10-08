<?php

/**
 * Validations rule base.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

use BadMethodCallException;

/**
 * Validations rule class base.
 */
class ValidationRule
{
    /** @var bool */
    protected $required = false;
    /** @var string default charset */
    protected $charset = 'UTF-8';
    /** @var string the error message */
    protected $error;
    /** @var string the field name */
    protected $field;
    /** @var string the error message template */
    protected $errMsgTpl;
    /** @var array the input data array */
    protected $input;
    /** @var array the array of parameters */
    protected $params;
    /** @var bool is the value valid? */
    protected $valid;
    /** @var mixed the field value */
    protected $value;
    /** @var bool was the validate executed? */
    protected $validated;

    /**
     * Constructor.
     *
     * @param string $field
     * @param array  $params
     * @param string $message
     */
    public function __construct(&$input, string $field, array $params = [], string $message = null)
    {
        if (!is_array($input)) {
            throw new BadMethodCallException(
                'Input must be an array of data'
            );
        }

        $this->charset = config_get('main.charset');
        $this->error = '';
        $this->errMsgTpl = $message ?? 'The value "@value" for field "@field" is invalid. Please enter a valid value.';
        $this->field = $field;
        $this->input = $input;
        $this->params = $params;
        $this->valid = false;
        $this->validated = false;
    }

    /**
     * Converts the value to string.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function stringifyValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        } elseif (is_bool($value)) {
            return '(bool)' . ($value ? 'True' : 'False');
        }

        return (string) $value;
    }

    /**
     * Validates the value.
     *
     * Must be extended in validation rule class.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return false;
    }

    /**
     * Returns the error message.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->error ?? '';
    }

    /**
     * Returns the rule name.
     *
     * @return string
     */
    public function getRuleName(): string
    {
        return get_class($this);
    }

    /**
     * Returns the result of the validation proccess.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->validated) {
            $this->validate();
        }

        return $this->valid;
    }

    /**
     * Proccess the validation of the field value in the input array.
     *
     * @return self
     */
    public function validate(): self
    {
        $this->error = '';
        $this->validated = true;

        if (
            !isset($this->input[$this->field])
            && !$this->required
        ) {
            $this->valid = true;

            return $this;
        }

        $this->value = $this->input[$this->field] ?? null;
        $this->valid = $this->isValueValid();

        if (!$this->valid) {
            $this->error = str_replace(
                ['@rule', '@field', '@value'],
                [$this->getRuleName(), $this->field, $this->stringifyValue($this->value)],
                $this->errMsgTpl
            );
        }

        return $this;
    }
}
