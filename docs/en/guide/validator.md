# 2-10 Forms and Validation

> What this solves: how to validate user-submitted data, how to present errors, and how to strip extra fields.
> Prerequisites: [Chapter 2-5 Controllers](controllers.md), [Chapter 2-9 Helpers and Global Functions](helper.md). About 15 minutes.
> All examples in this chapter are minimal snippets that can be dropped directly into the corresponding layer of a `MyProj` project; take [Component\Validator](../reference/Component-Validator.md) as authoritative for the rule list.

## Minimal example

```php
<?php declare(strict_types=1);
namespace MyProj\Business;

use DuckPhp\Foundation\SingletonTrait;

class UserBusiness
{
    use SingletonTrait;

    public function createUser(array $post): int
    {
        $data = Helper::ValidatorFilter($post, [
            'username' => 'required|minLen:3|maxLen:32',
            'email'    => 'required|email',
            'password' => 'required|minLen:6|confirmed',
        ], [
            'username.required'  => '请输入用户名',
            'email.email'        => '邮箱格式不正确',
            'password.confirmed' => '两次密码不一致',
        ]);
        // $data contains only the three fields declared above; other keys (like is_admin) are stripped
        return UserModel::_()->add($data);
    }
}
```

On validation failure `ValidatorFilter()` throws an exception directly, with the per-field error messages joined by `'; '`; the exception travels the mechanism of [Chapter 2-12](exception.md) into an error page or JSON — the Controller doesn't need any try/catch.

## How it works

### Three styles of validation

| Style | How it looks | Failure behavior | When to use |
| --- | --- | --- | --- |
| **Hand-written checks** | `if (trim($post['title'] ?? '') === '') { … }` | Entirely up to you | Minimal scenarios with one or two fields; errors must be merged into view data |
| **[Validator](../reference/Component-Validator.md) component** | `Helper::Validator()->init($rules)->valid($data)` | Returns an **error array** `['field' => 'message', …]`; an empty array means all passed | Form redisplay, per-field API messages |
| **Conditional throw (ThrowOn)** | `Helper::BusinessThrowOn($flag, 'message', code)` | Throws an exception when the condition holds | Business rules (insufficient balance, no permission), complementary to validation |

The three styles are not mutually exclusive: use Validator for field formats, conditional throw for business rules, hand-written checks for minimal scenarios.

### The three call forms of Validator

`DuckPhp\Component\Validator` (source `src/Component/Validator.php`) offers three entries for the same set of rules:

| Method | Returns | On failure |
|---|---|---|
| `valid($data)` | error array | doesn't throw; returns `['email' => 'Invalid email format', …]` |
| `check($data)` | nothing | throws `validator_exception_class` (default `\Exception`), message is the joined errors |
| `filter($data)` | data containing only the rule-declared fields | same as `check()`; on success `array_intersect_key` strips extra fields |

Error message lookup order: the `field.rule` key configured in `setMessage()` → the default string `Field [field] validation failed: rule`.

### Empty values and nullable

By default `validator_skip_empty = true`: when a non-`required` field's value is `null` or `''`, **the remaining rules are skipped**. Two explicit controls:

- Per field: `'phone' => 'nullable|regex:/^1\d{10}$/'` — empty values pass directly;
- Globally: `Validator::_()->skipEmpty(false)` — empty values also participate in validation.

### Unknown rules throw immediately

A misspelled rule name (e.g. `require` missing a `d`) throws `\InvalidArgumentException('Unknown validator rule: …')`, preventing validation from silently doing nothing.

### Division of labor: which layer validation belongs in

Per the iron rule of [Chapter 2-1 The Four Layers](layers.md):

- **Controller** ([Chapter 2-5](controllers.md)): only ferries `Helper::POST()` to the business layer; if you want the "error array redisplays the form" flow, you can call `valid()` here to get the errors and merge `$errors` into the view data.
- **Business**: the home of rules and validation. After `filter()` validates and strips extra fields, hand off to Model; business rules use `Helper::BusinessThrowOn()`.
- **Model**: does no validation (Chapter 2-8).

## Common patterns

### 1. Form redisplay: the error array goes into the view

```php
// Controller
public function add()
{
    $errors = [];
    if (Helper::POST()) {
        $errors = Helper::ValidatorValid(Helper::POST(), [
            'title' => 'required|minLen:1|maxLen:64',
        ], [
            'title.required' => '标题不能为空',
        ]);
        if (!$errors) {
            NoteBusiness::_()->create(Helper::POST());
            Helper::Show302('Note/index');
        }
    }
    Helper::Show(get_defined_vars(), 'note/add');   // $errors is usable in the view
}
```

```php
<?php // view/note/add.php ?>
<?php if (!empty($errors)): ?>
  <ul class="errors"><?php foreach ($errors as $field => $msg): ?>
    <li><?= __h($field) ?>：<?= __h($msg) ?></li>
  <?php endforeach; ?></ul>
<?php endif; ?>
```

### 2. API: per-field errors as JSON

```php
$errors = Helper::ValidatorValid(Helper::POST(), $rules, $messages);
if ($errors) {
    Helper::ShowJson(['code' => 1, 'errors' => $errors]);   // the frontend displays them by field
}
```

### 3. Validate + filter in one go (recommended)

```php
// Business
$data = Helper::ValidatorFilter($input, $rules, $messages);
return UserModel::_()->add($data);
```

`filter()` first runs `check()` (throwing on failure) and then strips undeclared fields — the standard way to "prevent users from smuggling extra fields into the database".

### 4. Custom callback rules

When the built-in rules aren't enough, use `callback:Class::method` (the callback receives `$value` and the whole `$data`; returning a truthy value passes):

```php
Helper::Validator()->init([
    'username' => 'required|callback:UserBusiness::checkUserExists',
])->setMessage([
    'username.callback' => '用户名不存在',
])->check($data);
```

```php
// in Business
public static function checkUserExists($value, $data): bool
{
    return UserModel::_()->getByUsername($value) !== null;
}
```

### 5. Throw on failure and hand over to the exception system

```php
Helper::Validator()
    ->init($rules)
    ->setMessage($messages)
    ->setExceptionClass(BusinessException::class)   // throw the project's own exception (Chapter 2-12)
    ->check(Helper::POST());
```

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| An optional field with an empty value reports "invalid format" | The field has rules like `email`/`int` but no `nullable`, and `skipEmpty` is turned off | Prepend `nullable` to the field rules, or keep `validator_skip_empty` at its default on |
| `confirmed` always fails | The confirmation field's name is not `<field>_confirmation` | Name the password confirmation input `password_confirmation` in the form |
| A misspelled rule name gives a 500 page | Unknown rules throw `\InvalidArgumentException` | Check spelling against the rule table in [Component\Validator](../reference/Component-Validator.md) |
| A user's extra `is_admin=1` got into the database | Direct `Model::add($_POST)` | Use `filter()` to take only the declared fields |
| The error array can't be read in the view | `Helper::Show()` didn't pass `$errors` in | `Helper::Show(get_defined_vars(), 'view')`, or explicitly `['errors' => $errors]` |
| The exception thrown by `check()` has the wrong type | `validator_exception_class` was not set | `setExceptionClass(BusinessException::class)`, or set it in the options |
| Field validation done in Model | Layer violation | Move validation up to Business; Model only reads and stores (Chapters 8, 13) |

## Next steps

- [Chapter 2-19 Using the User System](user.md): after login, how to know "who is current".
- [Chapter 2-12 Exceptions and Error Handling](exception.md): how the framework catches and presents the exceptions thrown by `check()`/`ThrowOn`.
- [Chapter 2-5 Controllers](controllers.md): `Helper::POST()` and the four ways of outputting.
- The reference manual: [DuckPhp\Component\Validator](../reference/Component-Validator.md), [DuckPhp\Foundation\Business\BusinessHelper](../reference/Foundation-Business-BusinessHelper.md)
