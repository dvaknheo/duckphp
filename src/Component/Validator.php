<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class Validator extends ComponentBase
{
    public $options = [
        'validator_exception_class' => \Exception::class,
        'validator_skip_empty' => true,
    ];
    protected $rules = [];
    protected $messages = [];

    //////////////////
    // instance API
    /**
     * Initialize the rule set.
     * @param array<string, string> $rules
     * @param object|null $context
     * @return static
     */
    public function init($rules = [], ?object $context = null)
    {
        parent::init([], $context);
        $this->rules = [];
        foreach ((array)$rules as $key => $value) {
            $this->rules[$key] = $value;
        }
        return $this;
    }
    /**
     * Set the rule set.
     * @param array<string, string> $rules
     * @return static
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }
    /**
     * Set custom error messages.
     * @param array<string, string> $messages
     * @return static
     */
    public function setMessage(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }
    /**
     * Set the exception class to throw.
     * @param class-string<\Throwable> $class
     * @return static
     */
    public function setExceptionClass($class)
    {
        $this->options['validator_exception_class'] = $class;
        return $this;
    }
    /**
     * Set whether to skip empty values for non-required fields.
     * @return static
     */
    public function skipEmpty(bool $flag = true)
    {
        $this->options['validator_skip_empty'] = $flag;
        return $this;
    }
    /**
     * Validate data, return error array. Empty array means all passed.
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function valid(array $data): array
    {
        return $this->_Valid($data, $this->rules, $this->messages);
    }
    /**
     * Validate data, throw exception on failure.
     * @param array<string, mixed> $data
     */
    public function check(array $data): void
    {
        $this->_Check($data, $this->rules, $this->messages);
    }
    /**
     * Validate data, throw exception on failure; on success return only fields declared in rules (filter extra fields).
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function filter(array $data): array
    {
        return $this->_Filter($data, $this->rules, $this->messages);
    }
    //////////////////
    // internal implementation
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     * @return array<string, string>
     */
    protected function _Valid(array $data, array $rules, array $messages = []): array
    {
        $errors = [];
        foreach ($rules as $field => $rule_str) {
            $value = $data[$field] ?? null;
            $rule_list = $this->parseRules($rule_str);
            $rule_names = array_column($rule_list, 0);
            $has_required = in_array('required', $rule_names, true);
            // nullable: empty value explicitly allowed, skip remaining rules
            if (in_array('nullable', $rule_names, true) && $this->isEmpty($value)) {
                continue;
            }
            if (!$has_required && $this->options['validator_skip_empty'] && $this->isEmpty($value)) {
                continue;
            }
            foreach ($rule_list as [$rule, $param]) {
                if (!$this->checkRule($rule, $value, $param, $data, $field)) {
                    $errors[$field] = $messages[$field.'.'.$rule] ?? sprintf('Field [%s] validation failed: %s', $field, $rule);
                    break;
                }
            }
        }
        return $errors;
    }
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     */
    protected function _Check(array $data, array $rules, array $messages = []): void
    {
        $errors = $this->_Valid($data, $rules, $messages);
        if ($errors) {
            $class = $this->options['validator_exception_class'] ?? \Exception::class;
            throw new $class(implode('; ', $errors));
        }
    }
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     * @return array<string, mixed>
     */
    protected function _Filter(array $data, array $rules, array $messages = []): array
    {
        $this->_Check($data, $rules, $messages);
        return array_intersect_key($data, array_flip(array_keys($rules)));
    }
    /**
     * Parse the rule string into a list of [rule name, parameter].
     * @return array<int, array{0:string, 1:?string}>
     */
    protected function parseRules(string $rule_str): array
    {
        $ret = [];
        foreach (explode('|', $rule_str) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $pos = strpos($part, ':');
            if ($pos === false) {
                $ret[] = [$part, null];
            } else {
                $ret[] = [substr($part, 0, $pos), substr($part, $pos + 1)];
            }
        }
        return $ret;
    }
    protected function isEmpty($value): bool
    {
        return $value === null || $value === '';
    }
    protected function checkRule(string $rule, $value, ?string $params, array $data = [], ?string $field = null): bool
    {
        switch ($rule) {
        case 'required':
            return !$this->isEmpty($value);
        case 'nullable':
            return true;
        case 'email':
            return is_scalar($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        case 'url':
            return is_scalar($value) && filter_var($value, FILTER_VALIDATE_URL) !== false;
        case 'int':
            return is_scalar($value) && filter_var($value, FILTER_VALIDATE_INT) !== false;
        case 'integer':
            return is_int($value);
        case 'numeric':
            return is_numeric($value);
        case 'string':
            return is_string($value);
        case 'array':
            return is_array($value);
        case 'bool':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
        case 'min':
            return is_numeric($value) && $value >= (int)$params;
        case 'max':
            return is_numeric($value) && $value <= (int)$params;
        case 'between':
            [$min, $max] = array_pad(explode(',', (string)$params), 2, null);
            return is_numeric($value) && $value >= (int)$min && $value <= (int)$max;
        case 'minLen':
            return is_scalar($value) && $this->strLen((string)$value) >= (int)$params;
        case 'maxLen':
            return is_scalar($value) && $this->strLen((string)$value) <= (int)$params;
        case 'length':
            return is_scalar($value) && $this->strLen((string)$value) === (int)$params;
        case 'in':
            return is_scalar($value) && in_array($value, explode(',', (string)$params));
        case 'confirmed':
            return is_scalar($value) && $value == ($data[$field.'_confirmation'] ?? null);
        case 'regex':
            return is_string($value) && (bool)preg_match((string)$params, $value);
        case 'date':
            return is_scalar($value) && strtotime((string)$value) !== false;
        case 'json':
            if (!is_string($value)) {
                return false;
            }
            json_decode($value);
            return json_last_error() === JSON_ERROR_NONE;
        case 'callback':
            return (bool)call_user_func($params, $value, $data);
        default:
            throw new \InvalidArgumentException('Unknown validator rule: '.$rule);
        }
    }
    protected function strLen(string $str): int
    {
        return function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);
    }
}
