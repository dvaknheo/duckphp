# 15 表单与数据验证

> 解决什么问题：用户提交的数据怎么校验、错误怎么呈现、多余字段怎么剔除。
> 前置：[第 10 章 控制器](controllers.md)、[第 14 章 Helper 与全局函数](helper.md)。预计 15 分钟。
> 本章示例均为最小片段，可直接放进 `MyProj` 工程的对应层；规则清单以 [Component\Validator](../reference/Component-Validator.md) 为准。

## 最小示例

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
        // $data 只含上面声明过的三个字段，其余键（如 is_admin）被剔除
        return UserModel::_()->add($data);
    }
}
```

校验失败时 `ValidatorFilter()` 直接抛异常，消息为各字段错误用 `'; '` 拼接；异常沿[第 18 章](exception.md)的机制变成错误页或 JSON，Controller 不用写 try/catch。

## 机制说明

### 三种验证口径

| 口径 | 写法 | 失败行为 | 适用场景 |
|---|---|---|---|
| **手写校验** | `if (trim($post['title'] ?? '') === '') { … }` | 完全自己定 | 一两个字段的极简场景；错误要拼进视图数据 |
| **Validator 组件** | `Helper::Validator()->init($rules)->valid($data)` | 返回**错误数组** `['字段' => '消息', …]`，空数组表示全通过 | 表单回显、API 逐字段提示 |
| **条件抛（ThrowOn）** | `Helper::BusinessThrowOn($flag, '消息', 代码)` | 满足条件就抛异常 | 业务规则（余额不足、无权操作），与验证互补 |

三种口径不互斥：字段格式用 Validator，业务规则用条件抛，极简场景手写。

### Validator 的三种调用形态

`DuckPhp\Component\Validator`（源码 `src/Component/Validator.php`）对同一组规则提供三个入口：

| 方法 | 返回 | 失败时 |
|---|---|---|
| `valid($data)` | 错误数组 | 不抛，返回 `['email' => '邮箱格式不正确', …]` |
| `check($data)` | 无 | 抛 `validator_exception_class`（默认 `\Exception`），消息为错误拼接 |
| `filter($data)` | 只含规则声明字段的数据 | 同 `check()`，通过后 `array_intersect_key` 剔除多余字段 |

错误消息查找顺序：`setMessage()` 里配的 `字段.规则` 键 → 默认串 `Field [字段] validation failed: 规则`。

### 空值与 nullable

默认 `validator_skip_empty = true`：非 `required` 字段值为 `null` 或 `''` 时**跳过其余规则**。两种显式控制：

- 字段级：`'phone' => 'nullable|regex:/^1\d{10}$/'` —— 空值直接放过；
- 全局级：`Validator::_()->skipEmpty(false)` —— 空值也参与校验。

### 未知规则即抛

规则名拼错（如 `require` 少个 `d`）会抛 `\InvalidArgumentException('Unknown validator rule: …')`，避免校验静默失效。

### 分工：验证写在哪一层

按[第 8 章 四层架构](layers.md)的铁律：

- **Controller**（[第 10 章](controllers.md)）：只搬运 `Helper::POST()` 给业务层；若要走「错误数组回显表单」流程，可以在这里 `valid()` 拿错误、把 `$errors` 拼进视图数据。
- **Business**：规则与校验的主场。`filter()` 校验 + 剔除多余字段后交给 Model；业务规则用 `Helper::BusinessThrowOn()`。
- **Model**：不做校验（第 13 章）。

## 常见写法

### 1. 表单回显：错误数组进视图

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
    Helper::Show(get_defined_vars(), 'note/add');   // $errors 在视图里可用
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

### 2. API：逐字段错误转 JSON

```php
$errors = Helper::ValidatorValid(Helper::POST(), $rules, $messages);
if ($errors) {
    Helper::ShowJson(['code' => 1, 'errors' => $errors]);   // 前端按字段显示
}
```

### 3. 校验 + 过滤一起做完（推荐）

```php
// Business
$data = Helper::ValidatorFilter($input, $rules, $messages);
return UserModel::_()->add($data);
```

`filter()` 先 `check()`（失败即抛）再剔除未声明字段，是「防止用户多提交字段入库」的标准做法。

### 4. 自定义回调规则

内置规则不够用时用 `callback:类::方法`（回调收 `$value` 与整个 `$data`，返回真值通过）：

```php
Helper::Validator()->init([
    'username' => 'required|callback:UserBusiness::checkUserExists',
])->setMessage([
    'username.callback' => '用户名不存在',
])->check($data);
```

```php
// Business 里
public static function checkUserExists($value, $data): bool
{
    return UserModel::_()->getByUsername($value) !== null;
}
```

### 5. 失败即抛并交给异常体系

```php
Helper::Validator()
    ->init($rules)
    ->setMessage($messages)
    ->setExceptionClass(BusinessException::class)   // 抛工程自己的异常（第 18 章）
    ->check(Helper::POST());
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 非必填字段空值却报「格式不正确」 | 该字段写了 `email`/`int` 等规则但没标 `nullable`，且关了 `skipEmpty` | 字段规则前加 `nullable`，或保持 `validator_skip_empty` 默认开启 |
| `confirmed` 永远失败 | 确认字段名不是 `<字段>_confirmation` | 表单里密码确认框命名 `password_confirmation` |
| 校验拼错规则名页面 500 | 未知规则抛 `\InvalidArgumentException` | 对照 [Component\Validator](../reference/Component-Validator.md) 规则表检查拼写 |
| 用户多提交了 `is_admin=1` 被入库 | 直接 `Model::add($_POST)` | 用 `filter()` 只取声明字段 |
| 错误数组在视图里取不到 | `Helper::Show()` 没把 `$errors` 传进去 | `Helper::Show(get_defined_vars(), 'view')` 或显式 `['errors' => $errors]` |
| `check()` 抛的异常类型不对 | 没设 `validator_exception_class` | `setExceptionClass(BusinessException::class)`，或在选项里配 |
| 在 Model 里做字段校验 | 越层了 | 校验上移 Business；Model 只查存（第 8、13 章） |

## 下一步

- [第 16 章 会话与用户/管理员体系](external-auth.md)：登录之后，怎么知道「当前是谁」。
- [第 18 章 异常与错误处理](exception.md)：`check()`/`ThrowOn` 抛出的异常，框架怎么接住、怎么呈现。
- [第 10 章 控制器](controllers.md)：`Helper::POST()` 与输出四方式。
- 参考手册：[DuckPhp\Component\Validator](../reference/Component-Validator.md)、[DuckPhp\Helper\BusinessHelperTrait](../reference/Helper-BusinessHelperTrait.md)
