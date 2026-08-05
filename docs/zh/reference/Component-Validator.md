# DuckPhp\Component\Validator

数据验证组件，支持声明式规则、自定义错误消息与回调验证。

## 简介

`Validator` 组件用于验证输入数据（表单、API 请求、业务数据）。规则以字符串声明（`'required|email|maxLen:255'`），支持 `|` 分隔多规则、`:` 传递参数、`callback` 自定义验证回调。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `validator_exception_class` | `\Exception::class` | `check()` / `filter()` 失败时抛出的异常类。 |
| `validator_skip_empty` | `true` | 非 `required` 字段为空值（`''`/`null`）时是否跳过其余规则。 |

## 实例 API

### 初始化与配置

```php
$v = Validator::_()
    ->setRules(['username' => 'required|minLen:1|maxLen:32'])
    ->setMessage(['username.minLen' => '用户名太短'])
    ->setExceptionClass(\RuntimeException::class)
    ->skipEmpty(false);
```

| 方法 | 说明 |
|---|---|
| `init(array $rules = [], ?object $context = null)` | 初始化规则集（清空后填充），返回 `$this` |
| `setRules(array $rules)` | 设置规则集，返回 `$this` |
| `setMessage(array $messages)` | 设置自定义错误消息（键：`字段.规则`），返回 `$this` |
| `setExceptionClass(string $class)` | 设置抛出的异常类，返回 `$this` |
| `skipEmpty(bool $flag = true)` | 设置非 required 空值是否跳过其余规则，返回 `$this` |

### 验证方法

| 方法 | 失败行为 | 返回值 | 用途 |
|---|---|---|---|
| `valid(array $data): array` | 不抛异常 | 错误数组（空 = 通过） | 收集错误自行处理（如 JSON 回传） |
| `check(array $data): void` | 抛异常 | 无 | 只校验，失败交给异常处理 |
| `filter(array $data): array` | 抛异常 | 过滤后的字段数据 | 校验 + 白名单清洗，直接入库 |

## 内置规则

| 规则 | 参数 | 说明 |
|---|---|---|
| `required` | - | 值非空（非 `null`、非 `''`） |
| `nullable` | - | 空值显式允许，跳过其余规则（字段级豁免） |
| `email` | - | 合法 Email |
| `url` | - | 合法 URL |
| `int` | - | 整数（`filter_var` 宽松判断） |
| `integer` | - | 严格 `is_int` |
| `numeric` | - | 数字（含浮点/数字字符串） |
| `string` | - | 字符串 |
| `array` | - | 数组 |
| `bool` | - | 布尔（`'1'`/`'true'`/`'on'`/`'yes'`/`true` 等） |
| `min:n` | `n` | 数值 ≥ n |
| `max:n` | `n` | 数值 ≤ n |
| `between:a,b` | `a,b` | 数值在 a ~ b 之间 |
| `minLen:n` | `n` | 字符串长度 ≥ n（`mb_strlen`） |
| `maxLen:n` | `n` | 字符串长度 ≤ n |
| `length:n` | `n` | 字符串长度 = n |
| `in:a,b,c` | `a,b,c` | 值在列表中（松散比较） |
| `confirmed` | - | 与 `字段_confirmation` 值一致（如 `pwd` ↔ `pwd_confirmation`） |
| `regex:/.../` | 正则 | 匹配正则（自带定界符） |
| `date` | - | 可被 `strtotime` 解析的日期 |
| `json` | - | 合法 JSON 字符串 |
| `callback:Class::method` | 回调 | 回调 `function($value, $data): bool`，返回 true 通过 |

未知规则抛出 `\InvalidArgumentException`（防止规则拼写错误静默通过）。

## 使用方式

### 错误数组（`valid`）— 前端逐字段提示

```php
$errors = Validator::_()->setRules([
    'name'  => 'required|maxLen:50',
    'phone' => 'regex:/^1\d{10}$/',
])->valid($_POST);

if ($errors) {
    Helper::ShowJson(['code' => 1, 'errors' => $errors]);
}
```

### 抛异常（`check`）— 框架统一异常处理

```php
Validator::_()
    ->setRules(['email' => 'required|email'])
    ->setExceptionClass(BusinessException::class)
    ->check($_POST);
```

### 校验 + 清洗（`filter`）— 白名单过滤后入库

```php
$data = Validator::_()->setRules([
    'username' => 'required|minLen:2|maxLen:20',
    'email'    => 'required|email',
])->filter($_POST);
// 只返回规则中出现的字段，多余字段（如 is_admin）被剔除
```

### 自定义回调验证

```php
class MyBusiness
{
    public static function checkUserExists($value, $data): bool
    {
        return MyUserModel::_()->getByUsername($value) !== null;
    }
}

Validator::_()->setRules([
    'username' => 'required|callback:MyBusiness::checkUserExists',
])->setMessage([
    'username.callback' => '用户不存在',
])->check($data);
```

### Business 层便捷方法

```php
Helper::Validator()->setRules([...])->check($data);   // 获取实例
Helper::ValidatorValid($data, $rules, $messages);      // 错误数组
Helper::ValidatorCheck($data, $rules, $messages);      // 抛异常
Helper::ValidatorFilter($data, $rules, $messages);     // 过滤
```
