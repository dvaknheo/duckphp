# DuckPhp\Component\Validator

## Introduction

`Validator` is DuckPHP's data validation component, using a declarative "field => rule string" style: rule names are separated by `|`, and rules that need parameters take them via `:param` (e.g. `'age' => 'required|int|min:18'`).

The component offers three usage flavors:

- `valid()`: validate and return an error array (empty when clean, for you to handle);
- `check()`: throw an exception directly on validation failure (the exception class is settable via `validator_exception_class`);
- `filter()`: after validation passes, return only the fields declared in the rules (extra fields are filtered out).

It is an ordinary component: use it as `Validator::_()->init($rules)->…`; no persistent global initialization is needed.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class Validator extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Description |
|---|---|---|
| `validator_exception_class` | `\Exception::class` | The exception class thrown by `check()`/`filter()` on validation failure (must be `new`able and accept one string message). |
| `validator_skip_empty` | `true` | Whether to skip the remaining rules for non-`required` fields whose value is empty (`null` or `''`). |

## Usage

### Basic validation

```php
use DuckPhp\Component\Validator;

$rules = [
    'email'    => 'required|email',
    'password' => 'required|minLen:6|confirmed',
    'age'      => 'nullable|int|between:18,60',
    'tags'     => 'array',
];

$v = Validator::_()->init($rules);
$errors = $v->valid($_POST);
if ($errors) {
    // $errors: ['email' => '…', …] (see the error-message rules below)
}
```

### Throw on failure

```php
Validator::_()->init($rules)->setMessage([
    'email.required' => '请填写邮箱',
    'email.email'    => '邮箱格式不正确',
])->check($_POST);
// on failure: throws validator_exception_class (default \Exception), message is the errors joined with '; '
```

### Get the declared fields back after validation (filter out extras)

```php
$safe = Validator::_()->init($rules)->filter($_POST);
// contains only fields that appear in $rules
```

## Configuration example

```php
use DuckPhp\Component\Validator;

// adjust behavior fluently
$v = Validator::_()
    ->setRules(['code' => 'required|regex:/^[A-Z]{2}\d{4}$/'])
    ->setMessage(['code.regex' => '编码格式错误'])
    ->setExceptionClass(\InvalidArgumentException::class)
    ->skipEmpty(false); // keep running later rules even for empty values

// then
$errors = $v->valid($data);      // returns the error array
// or
$v->check($data);                // throws on any error
```

## Caveats

- **Rule string syntax**: `rule1|rule2:param|rule3`. Everything after `:` is the parameter; multi-parameter rules use commas, e.g. `between:1,10`, `in:1,2,3`; the `params` of `regex` is a full regex (`regex:/^…$/`).
- **`nullable`**: an empty value (`null`/`''`) passes outright and skips the field's remaining rules.
- **`skipEmpty` semantics**: with the default on, a non-`required` field is skipped whenever it is empty (no errors even if it has other rules); an empty `required` field errors. Turn off with `->skipEmpty(false)`.
- **`confirmed`**: checks that `field` equals `field_confirmation` (loose comparison `==`).
- **`bool`**: uses `FILTER_VALIDATE_BOOLEAN` + `FILTER_NULL_ON_FAILURE`, so `1/0/true/false/on/off/yes/no` etc. all count as booleans.
- **`min/max/between/minLen/maxLen/length`**: the numeric ones compare numerically; `minLen/maxLen/length` act on strings (multibyte-aware when `mb_strlen` is available).
- **Error messages**: default messages look like `Field [field] validation failed: rule`; custom message keys are `field.rule` (see the example above). The array returned by `valid()` is keyed by field and keeps only the first failed rule per field.
- **Unknown rules**: `checkRule()` throws `InvalidArgumentException` ("Unknown validator rule: …").
- Note: `Validator::init()` overrides the signature of `ComponentBase::init()` (`init($rules = [], ?object $context = null)`); it calls `parent::init([], $context)` first, then loads the rule set.

## All options

```php
'validator_exception_class' => \Exception::class,
'validator_skip_empty' => true,
```

## Methods

### Public methods

    public function init($rules = [], ?object $context = null)
Initialize the validator: first run the component base-class flow (empty options, optional context), then load `$rules` (field => rule string), clearing any old rules; returns itself.

    public function setRules(array $rules)
Replace the current rule set wholesale; returns itself.

    public function setMessage(array $messages)
Set custom error messages (keys like `field.rule`); returns itself.

    public function setExceptionClass($class)
Set the exception class thrown on validation failure; equivalent to changing the `validator_exception_class` option; returns itself.

    public function skipEmpty(bool $flag = true)
Set whether empty values of non-`required` fields are skipped; equivalent to changing the `validator_skip_empty` option; returns itself.

    public function valid(array $data): array
Validate the data against the current rules, returning the error array (field => message); an empty array means all passed.

    public function check(array $data): void
Validate against the current rules; any error throws `validator_exception_class`, with the message being the errors joined by `; `.

    public function filter(array $data): array
Runs `check()` first (throws on failure); after passing, returns only the fields declared in the rules (filtering out extras).

### Protected methods

    protected function _Valid(array $data, array $rules, array $messages = []): array
The validation implementation: parses each field's rule string, handles the `required`/`nullable`/`skipEmpty` semantics, looks messages up by `field.rule` on failure and records only the first failure per field; called by `valid()`.

    protected function _Check(array $data, array $rules, array $messages = []): void
Throws when `_Valid` has errors; called by `check()`/`filter()`.

    protected function _Filter(array $data, array $rules, array $messages = []): array
Runs `_Check` first, then filters the data with `array_intersect_key` against the rule keys; called by `filter()`.

    protected function parseRules(string $rule_str): array
Split a rule string of the form `a|b:1|c:2` into a `[rule name, params|null]` list; split on `|`, break params at the first `:`, ignore empty segments.

    protected function isEmpty($value): bool
Decides "empty value": `null` or `''` (shared by `required`/`skipEmpty`/`nullable`).

    protected function checkRule(string $rule, $value, ?string $params, array $data = [], ?string $field = null): bool
Execute one rule: `required/nullable/email/url/int/integer/numeric/string/array/bool/min/max/between/minLen/maxLen/length/in/confirmed/regex/date/json/callback`; unknown rules throw `InvalidArgumentException`.

    protected function strLen(string $str): int
String length: prefers `mb_strlen`, falls back to `strlen`.

## Related links

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the component base class (the `_()` entry and the options mechanism)
- [Guide: Validator validation](<../guide/validator.md>) — tutorial-style usage notes
