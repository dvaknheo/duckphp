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
    // 实例 API
    /**
     * 初始化规则集。
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
     * 设置规则集。
     * @param array<string, string> $rules
     * @return static
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }
    /**
     * 设置自定义错误消息。
     * @param array<string, string> $messages
     * @return static
     */
    public function setMessage(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }
    /**
     * 设置抛出的异常类。
     * @param class-string<\Throwable> $class
     * @return static
     */
    public function setExceptionClass($class)
    {
        $this->options['validator_exception_class'] = $class;
        return $this;
    }
    /**
     * 设置非 required 空值是否跳过其余规则。
     * @return static
     */
    public function skipEmpty(bool $flag = true)
    {
        $this->options['validator_skip_empty'] = $flag;
        return $this;
    }
    /**
     * 验证数据，返回错误数组。空数组表示全部通过。
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function valid(array $data): array
    {
        return $this->_Valid($data, $this->rules, $this->messages);
    }
    /**
     * 验证数据，失败抛异常。
     * @param array<string, mixed> $data
     */
    public function check(array $data): void
    {
        $this->_Check($data, $this->rules, $this->messages);
    }
    /**
     * 验证数据，失败抛异常；通过则返回规则中出现的字段数据（过滤掉多余字段）。
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function filter(array $data): array
    {
        return $this->_Filter($data, $this->rules, $this->messages);
    }
    //////////////////
    // 内部实现
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
            // nullable：空值显式允许，跳过其余规则
            if (in_array('nullable', $rule_names, true) && $this->isEmpty($value)) {
                continue;
            }
            if (!$has_required && $this->options['validator_skip_empty'] && $this->isEmpty($value)) {
                continue;
            }
            foreach ($rule_list as [$rule, $param]) {
                if (!$this->checkRule($rule, $value, $param, $data, $field)) {
                    $errors[$field] = $messages[$field.'.'.$rule] ?? sprintf('字段 [%s] 验证失败: %s', $field, $rule);
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
     * 解析规则字符串为 [规则名, 参数] 列表。
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
