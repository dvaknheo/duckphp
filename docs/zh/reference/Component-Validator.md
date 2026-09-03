# DuckPhp\Component\Validator

## 简介

`Validator` 是 DuckPHP 的数据验证组件，采用“字段 => 规则字符串”的声明式写法：规则名之间用 `|` 分隔，需要参数的规则用 `:参数` 引入（例如 `'age' => 'required|int|min:18'`）。

组件提供三种使用口径：

- `valid()`：校验并返回错误数组（无错时为空数组，便于自行处理）；
- `check()`：校验失败时直接抛出异常（异常类可用 `validator_exception_class` 指定）；
- `filter()`：校验通过后，只返回规则声明过的字段（顺带过滤多余字段）。

它是普通组件：通过 `Validator::_()->init($rules)->…` 使用即可，无需常驻全局初始化。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class Validator extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `validator_exception_class` | `\Exception::class` | `check()`/`filter()` 校验失败时抛出的异常类（必须可被 `new` 且接受一个字符串消息）。 |
| `validator_skip_empty` | `true` | 非 `required` 字段值为空（`null` 或 `''`）时是否跳过其余规则。 |

## 使用方式

### 基本校验

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
    // $errors: ['email' => '…', …]（见下方错误消息规则）
}
```

### 失败即抛异常

```php
Validator::_()->init($rules)->setMessage([
    'email.required' => '请填写邮箱',
    'email.email'    => '邮箱格式不正确',
])->check($_POST);
// 失败：抛出 validator_exception_class（默认 \Exception），消息为各错误 '; ' 拼接
```

### 校验后取回声明字段（过滤多余字段）

```php
$safe = Validator::_()->init($rules)->filter($_POST);
// 只包含 $rules 中出现过的字段
```

## 配置示例

```php
use DuckPhp\Component\Validator;

// 链式调整行为
$v = Validator::_()
    ->setRules(['code' => 'required|regex:/^[A-Z]{2}\d{4}$/'])
    ->setMessage(['code.regex' => '编码格式错误'])
    ->setExceptionClass(\InvalidArgumentException::class)
    ->skipEmpty(false); // 空值也继续执行后续规则

// 然后
$errors = $v->valid($data);      // 返回错误数组
// 或
$v->check($data);                // 有错即抛
```

## 注意事项

- **规则字符串语法**：`rule1|rule2:param|rule3`。`:` 之后为参数；多条参数用逗号分隔的规则有 `between:1,10`、`in:1,2,3`；`regex` 的 `params` 是完整正则（`regex:/^…$/`）。
- **`nullable`**：值为空（`null`/`''`）时直接放行并跳过该字段其余规则。
- **`skipEmpty` 语义**：默认开启时，非 `required` 字段只要为空就跳过（含该字段有其它规则也不报）；`required` 字段为空则报错。可 `->skipEmpty(false)` 关闭。
- **`confirmed`**：校验 `字段名` 与 `字段名_confirmation` 两值相等（宽松比较 `==`）。
- **`bool`**：使用 `FILTER_VALIDATE_BOOLEAN` + `FILTER_NULL_ON_FAILURE`，即 `1/0/true/false/on/off/yes/no` 等皆可视为布尔。
- **`min/max/between/minLen/maxLen/length`**：数值类用数值比较；`minLen/maxLen/length` 作用于字符串（有 `mb_strlen` 时按多字节计长）。
- **错误消息**：默认消息形如 `Field [字段] validation failed: 规则`；自定义消息键为 `字段.规则`（见上例）。`valid()` 返回数组按字段为键、只保留每个字段第一个失败规则。
- **未知规则**：`checkRule()` 抛 `InvalidArgumentException`（“Unknown validator rule: …”）。
- 注意：`Validator::init()` 覆盖了 `ComponentBase::init()` 的签名（`init($rules = [], ?object $context = null)`），先调 `parent::init([], $context)` 再装载规则集。

## 全部选项

```php
'validator_exception_class' => \Exception::class,
'validator_skip_empty' => true,
```

## 方法列表

### 公共方法

    public function init($rules = [], ?object $context = null)
初始化校验器：先按组件基类流程初始化（空选项、可选上下文），再把 `$rules`（字段 => 规则字符串）装入并清空旧规则；返回自身。

    public function setRules(array $rules)
整体替换当前规则集；返回自身。

    public function setMessage(array $messages)
设置自定义错误消息（键形如 `字段.规则`）；返回自身。

    public function setExceptionClass($class)
设置校验失败抛出的异常类，等价于修改选项 `validator_exception_class`；返回自身。

    public function skipEmpty(bool $flag = true)
设置是否跳过非 `required` 的空值字段，等价于修改选项 `validator_skip_empty`；返回自身。

    public function valid(array $data): array
按当前规则校验数据，返回错误数组（字段 => 消息）；空数组表示全部通过。

    public function check(array $data): void
按当前规则校验，任一错误则以 `validator_exception_class` 抛异常，消息为各错误以 `; ` 拼接。

    public function filter(array $data): array
先 `check()`（失败抛异常），通过后仅返回规则中声明过的字段（过滤多余字段）。

### 受保护方法

    protected function _Valid(array $data, array $rules, array $messages = []): array
校验实现：逐字段解析规则串、处理 `required`/`nullable`/`skipEmpty` 语义，失败按 `字段.规则` 取消息并只记录每个字段首个失败；供 `valid()` 调用。

    protected function _Check(array $data, array $rules, array $messages = []): void
`_Valid` 有错即抛异常；供 `check()`/`filter()` 调用。

    protected function _Filter(array $data, array $rules, array $messages = []): array
先 `_Check`，再按规则键 `array_intersect_key` 过滤数据；供 `filter()` 调用。

    protected function parseRules(string $rule_str): array
把 `a|b:1|c:2` 形式的规则串拆为 `[规则名, 参数|null]` 列表；按 `|` 切分、首个 `:` 拆参数、空段忽略。

    protected function isEmpty($value): bool
判断“空值”：`null` 或 `''`（`required`/`skipEmpty`/`nullable` 共用）。

    protected function checkRule(string $rule, $value, ?string $params, array $data = [], ?string $field = null): bool
执行单条规则：`required/nullable/email/url/int/integer/numeric/string/array/bool/min/max/between/minLen/maxLen/length/in/confirmed/regex/date/json/callback`；未知规则抛 `InvalidArgumentException`。

    protected function strLen(string $str): int
计算字符串长度：优先 `mb_strlen`，否则 `strlen`。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件基类（`_()` 入口与选项机制）
- [guide：Validator 校验](<../guide/validator.md>) — 教程向的使用说明
