<?php

/**
 * Validation of user-assigned data.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   2.0.0
 */

namespace Springy\Validation;

use Springy\HTTP\Input;
use Springy\Utils\MessageContainer;

/**
 * Class for validation of user-assigned data.
 */
class Validator
{
    /** @var MessageContainer the validation error messages */
    protected $errors;
    /** @var array user-assigned data */
    protected $input;
    /** @var array the array of validation rules */
    protected $rules;

    /**
     * Constructor.
     *
     * @param array $input user-assigned data
     * @param array $rules validation rules
     */
    public function __construct(array $input = [], array $rules = [])
    {
        $this->setInput($input);
        $this->rules = $rules;
        $this->errors = new MessageContainer();
    }

    /**
     * Applies all rules for given field.
     *
     * @param string       $field name of the field.
     * @param array|string $rules an array or a string with the rules delimited by pipe char '|'.
     *
     * @return void
     */
    protected function applyRules(string $field, $rules)
    {
        foreach ($this->explodeRules($rules, $field) as $rule) {
            if (!$rule->validate($this->input)) {
                $this->errors->add($field, $rule->getErrorMessage());
            }
        }
    }

    /**
     * Explodes and parses the rules.
     *
     * @param string|array $rules an array or a string with the rules delimited by pipe char '|'.
     * @param string       $field
     *
     * @return array an array with Rule objects.
     */
    protected function explodeRules($rules, string $field)
    {
        $explodedRules = [];

        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        foreach ($rules as $index => $rule) {
            $rule = $this->parseRule($index, $rule);
            $explodedRules[] = new Rule($field, $rule['n'], $rule['p'], $rule['m']);
        }

        return $explodedRules;
    }

    /**
     * Compiles the actual rule to an friendly array.
     *
     * @param mixed        $name
     * @param array|string $rule
     *
     * @throws BadMethodCallException
     *
     * @return array
     */
    protected function parseRule($name, $rule): array
    {
        // The rule is an array?
        if (is_array($rule)) {
            $params = $rule['params'] ?? '';

            return [
                'n' => $name,
                'p' => is_array($params) ? $params : (array) explode(',', $params),
                'm' => $rule['message'] ?? null,
            ];
        }

        // The rule is an object (Json)?
        if (is_object($rule)) {
            $params = $rule->params ?? '';

            return [
                'n' => $name,
                'p' => is_array($params) ? $params : (array) explode(',', $params),
                'm' => $rule->message ?? null,
            ];
        }

        // Converts the rule to an array
        $rule = explode(':', $rule);
        if ($rule === false) {
            throw new \BadMethodCallException('Validation rule empty.');
        }

        return [
            'n' => $rule[0],
            'p' => isset($rule[1]) ? (array) explode(',', $rule[1]) : [],
            'm' => $rule[2] ?? null,
        ];
    }

    /**
     * An inverted alias to validate.
     *
     * @return bool The inverted value of validate method.
     */
    public function fails()
    {
        return !$this->validate();
    }

    /**
     * Gets the generated errors.
     *
     * @return Springy\Utils\MessageContainer
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns the data provided by the user.
     *
     * @return array
     */
    public function getInput(): array
    {
        return $this->input;
    }

    /**
     * Gets the validation rules.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * An alias for validate method.
     *
     * @return bool
     */
    public function passes(): bool
    {
        return $this->validate();
    }

    /**
     * Sets the data provided by the user.
     *
     * @param mixed $input An array or a instance of Springy\HTTP\Input object.
     */
    public function setInput($input)
    {
        $this->input = ($input instanceof Input) ? $input->all() : $input;
    }

    /**
     * Sets the validation rules.
     *
     * @param array $rules
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Run the validation.
     *
     * @return bool Returns true if no errors in validation or false if has errors.
     */
    public function validate()
    {
        $this->errors->clear();

        foreach ($this->rules as $field => $rules) {
            $this->applyRules($field, $rules);
        }

        return !$this->errors->hasAny();
    }
}
