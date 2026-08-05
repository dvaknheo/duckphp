# 数据验证（Validator）

DuckPhp 提供 `DuckPhp\Component\Validator` 组件做数据验证，支持声明式规则字符串、自定义错误消息、回调验证与白名单过滤。

## 快速开始

```php
use DuckPhp\Component\Validator;

$v = Validator::_()->setRules([
    'username' => 'required|minLen:1|maxLen:32',
    'email'    => 'required|email',
    'age'      => 'int|between:1,120',
]);

$errors = $v->valid($_POST);   // 错误数组，[] 表示全部通过
```

规则用 `|` 分隔多条，`:` 后跟参数。完整规则列表见 [Component\Validator 参考手册](../reference/Component-Validator.md)。

## 三种验证方法

| 方法 | 行为 | 适用场景 |
|------|------|---------|
| `valid($data)` | 返回错误数组，不抛异常 | 需要把错误发给前端（表单回显、JSON 逐字段提示） |
| `check($data)` | 失败抛异常（默认 `\Exception`） | 交给框架统一异常处理 |
| `filter($data)` | 失败抛异常；通过后只返回规则中声明的字段 | 校验 + 白名单清洗，防止多余字段入库 |

## Controller 层：表单验证

```php
public function create()
{
    $v = Helper::Validator()->setRules([
        'username' => 'required|minLen:3|maxLen:32',
        'email'    => 'required|email',
        'password' => 'required|minLen:6|confirmed',
    ])->setMessage([
        'username.required' => '请输入用户名',
        'email.email'       => '邮箱格式不正确',
        'password.confirmed' => '两次密码不一致',
    ]);

    $errors = $v->valid(Helper::POST());
    if ($errors) {
        Helper::ShowJson(['code' => 1, 'errors' => $errors]);
    }
    // 通过后继续……
}
```

## Controller 层：抛异常简化

验证失败交给 `ExceptionReporter` 统一处理（页面显示错误页、API 转 JSON），Controller 无需写 try/catch：

```php
public function create()
{
    Helper::Validator()
        ->setRules([
            'username' => 'required|minLen:3|maxLen:32',
            'email'    => 'required|email',
        ])
        ->setExceptionClass(BusinessException::class)
        ->check(Helper::POST());

    MyBusiness::_()->createUser(Helper::POST());
}
```

## Business 层：业务数据验证 + 过滤

```php
public function createUser(array $input): int
{
    $data = Helper::Validator()->setRules([
        'username' => 'required|minLen:3|maxLen:32',
        'email'    => 'required|email',
    ])->filter($input);
    // $data 只含 username/email，其他字段（如 is_admin）被剔除
    return MyUserModel::_()->add($data);
}
```

或使用便捷方法：

```php
$errors = Helper::ValidatorValid($input, $rules, $messages);   // 错误数组
Helper::ValidatorCheck($input, $rules, $messages);             // 抛异常
$clean  = Helper::ValidatorFilter($input, $rules, $messages);  // 过滤
```

## 自定义回调验证

内置规则不够用时，用 `callback:Class::method` 调用自定义逻辑（回调接收 `$value` 与整个 `$data`，返回 `true` 通过）：

```php
class MyBusiness
{
    public static function checkUserExists($value, $data): bool
    {
        return MyUserModel::_()->getByUsername($value) !== null;
    }
    public static function checkPhoneUnique($value, $data): bool
    {
        return MyUserModel::_()->getByPhone($value) === null;   // 手机号唯一
    }
}

Helper::Validator()->setRules([
    'username' => 'required|callback:MyBusiness::checkUserExists',
    'phone'    => 'required|regex:/^1\d{10}$/|callback:MyBusiness::checkPhoneUnique',
])->setMessage([
    'username.callback' => '用户名不存在',
    'phone.callback'    => '手机号已被注册',
])->check($data);
```

## 空值处理

默认 `validator_skip_empty = true`：非 `required` 字段为空值（`''`/`null`）时**跳过其余规则**。两种豁免方式：

```php
// 1. 字段级显式豁免（即使全局关闭也生效）
'phone' => 'nullable|regex:/^1\d{10}$/',

// 2. 全局开关（默认 true）
Validator::_()->skipEmpty(false);   // 关闭：空值也参与校验
```

## 未知规则

规则拼写错误（如 `require` 少个 `d`）会抛出 `\InvalidArgumentException`，避免校验静默失效。

## 更多

- [Component\Validator 参考手册](../reference/Component-Validator.md) — 全部规则与 API 说明
