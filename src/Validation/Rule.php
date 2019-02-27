<?php
/**
 * Valuation rule class for validation of user-assigned data.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation;

use Springy\Utils\StringUtils;

class Rule
{
    use StringUtils;

    /** @var string default charset */
    protected $charset = 'UTF-8';
    /** @var string the error message */
    protected $error;
    /** @var string the field name */
    protected $field;
    /** @var string the validation method name */
    protected $method;
    /** @var array the array of parameters */
    protected $params;
    /** @var string the validation rule */
    protected $rule;
    /** @var string the error message template */
    protected $errMsgTpl;

    /**
     * Constructor.
     *
     * @param string $field
     * @param string $rule
     *
     * The $rule can be a string in the format 'ruleName:param1,param2[,paramN]'
     */
    public function __construct(string $field, string $rule, array $params = [], string $message = null)
    {
        $this->charset = config_get('main.charset');
        $this->errMsgTpl = $message ?? 'The value @value for field @field is invalid. Please enter a valid value.';
        $this->error = '';
        $this->field = $field;
        $this->params = $params;
        $this->rule = $rule;
        $this->method = $this->parseMethod();
    }

    /**
     * Generates the method name for the rule.
     *
     * @return string
     */
    protected function parseMethod(): string
    {
        $method = 'validate'.str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $this->rule))));

        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" has no equivalent method for validation.');
        }

        return $method;
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
            return '(bool)'.($value ? 'True' : 'False');
        }

        return (string) $value;
    }

    /**
     * Validates whether the value has only letters.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateAlpha($value): bool
    {
        return preg_match('/^\pL+$/u', $value);
    }

    /**
     * Validates whether the value has only letters and numbers.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateAlphaNum($value): bool
    {
        return preg_match('/^[\pL\pN]+$/u', $value);
    }

    /**
     * Validates if the value is between the minimum and maximum range.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateBetween($value): bool
    {
        if (count($this->params) < 2) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" require 2 parameters '.count($this->params).' given.');
        }

        return ($value >= $this->params[0]) && ($value <= $this->params[1]);
    }

    /**
     * Validates if the value is a valid date.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateDate($value): bool
    {
        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * Validates if the value differs from that of another field.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateDifferent($value): bool
    {
        if (!isset($this->params[0])) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" require comparison field name parameter no one given.');
        }

        return $value !== ($this->input[$this->params[0]] ?? null);
    }

    /**
     * Validates whether the value is an email address.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateEmail($value): bool
    {
        return $this->isValidEmailAddress($value);
    }

    /**
     * Validates if the value is in a list.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateIn($value): bool
    {
        return in_array($value, $this->params);
    }

    /**
     * Validates whether the value is an integer.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateInteger($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validates whether the value is an IP address.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateIp($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validates if the text length is within the allowed range.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateLengthBetween($value): bool
    {
        if (count($this->params) < 2) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" require 2 parameters '.count($this->params).' given.');
        }

        $length = mb_strlen($value, $this->charset);

        return ($length >= $this->params[0]) && ($length <= $this->params[1]);
    }

    /**
     * Validates if the value meets the maximum allowed.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateMax($value): bool
    {
        if (!isset($this->params[0])) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" requires the maximum value parameter no one given.');
        }

        return $value <= $this->params[0];
    }

    /**
     * Validates if text matches the longest length allowed.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateMaxLength($value): bool
    {
        if (!isset($this->params[0])) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" requires the maximum length parameter no one given.');
        }

        return mb_strlen($value, $this->charset) <= $this->params[0];
    }

    /**
     * Validates if the value meets the minimum required.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateMin($value): bool
    {
        if (!isset($this->params[0])) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" requires the minimum value parameter no one given.');
        }

        return $value >= $this->params[0];
    }

    /**
     * Validates if the text has the shortest required length.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateMinLength($value): bool
    {
        if (!isset($this->params[0])) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" requires the minimum length parameter no one given.');
        }

        return mb_strlen($value, $this->charset) >= $this->params[0];
    }

    /**
     * Validates whether the value is numeric.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateNumeric($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validates if the value is not in a list.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateNotIn($value): bool
    {
        return !in_array($value, $this->params);
    }

    /**
     * Validate value using regular expression.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateRegex($value): bool
    {
        return preg_match(implode(',', $this->params), $value);
    }

    /**
     * Validates if the field is not null and not an empty string.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateRequired($value): bool
    {
        return ($value !== null) && ($value !== '');
    }

    /**
     * Validates whether the value is the same as that of another field.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateSame($value, array $input): bool
    {
        if (!isset($this->params[0])) {
            throw new \BadMethodCallException('Validation rule "'.$this->rule.'" require comparison field name parameter no one given.');
        }

        return $value === ($input[$this->params[0]] ?? null);
    }

    /**
     * Validates if the value is a URL.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateUrl($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
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

    public function getRuleName(): string
    {
        return $this->rule;
    }

    public function setRuleName(string $name)
    {
        $this->rule = $name;
        $this->method = $this->parseMethod();
    }

    /**
     * Validates the value into the rule.
     *
     * @param array $fields
     *
     * @return bool
     */
    public function validate(array $fields): bool
    {
        $this->error = '';

        if ($this->method === null) {
            $this->parseMethod();
        }

        if (!isset($fields[$this->field]) && $this->method != 'validateRequired') {
            return true;
        }

        $value = $fields[$this->field] ?? null;
        $valid = call_user_func(
            [$this, $this->method],
            $value,
            $fields
        );

        if (!$valid) {
            $this->error = str_replace(
                ['@field', '@rule', '@value'],
                [$this->field, $this->rule, $this->stringifyValue($value)],
                $this->errMsgTpl
            );
        }

        return $valid;
    }
}
