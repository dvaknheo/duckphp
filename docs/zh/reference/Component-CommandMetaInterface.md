# DuckPhp\Component\CommandMetaInterface

## 简介

`CommandMetaInterface` 是「命令表元数据」契约接口：命令类实现它，就能用**代码**返回自己的命令表（命令名 → 描述），供 [DuckPhp\Component\Command](Component-Command.md) 收集 CLI 帮助列表时直接取用，而不必靠反射去读方法名与 `@command_desc` 注释。

典型场景：命令是动态生成的（按配置/插件拼出来的），或者描述要运行时翻译——注释写不出来的地方就用它。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`interface CommandMetaInterface`
- 唯一成员是 `__commandMeta()`，没有常量、没有继承。

## 使用方式

```php
use DuckPhp\Component\CommandMetaInterface;

class MyCommand implements CommandMetaInterface
{
    public function __commandMeta(): array
    {
        // 命令名 => 描述（键名不含 method_prefix）
        return [
            'ping'  => 'ping the service',
            'clean' => '[[command.clean|clean the cache]]',   // 支持 [[key|fallback]] 翻译
        ];
    }
    public function command_ping() { }
    public function command_clean() { }
}
```

注册进 Console 后（`'cmd' => [MyCommand::class => 'command_']`，或 `regConsoleCommand()`），`command help` 就会列出上表。

## 注意事项

1. **有它就整体接管**：`Command::getCommandsByClass()` 先 `hasMethod('__commandMeta')`，命中就**直接返回它的结果**——`console_command_classes` 里给该类配的“方法前缀”不再参与，方法上的 `@command_desc` 也不再解析。内部固定用 `command_` 前缀（即 `Command::__commandMeta()` 自己的实现就是按 `command_` 反射本类）。
2. **键是「命令名」不是方法名**：`Command` 只用返回的键当命令词，所以键名自己去掉 `command_` 前缀；键里也不要带命名空间（命名空间由 `Console` 的命令注册表决定）。
3. **返回 `array<string, mixed>`**：值是描述字符串，取值处会经 `translateCommandDesc()`/`langText()`，因此 `[[key|fallback]]` 占位符可以照用。
4. **本接口是「建议」，不是硬性要求**：`Command::getCommandsByClass()` 靠 `hasMethod('__commandMeta')` 鸭子类型识别——**不 `implements` 本接口、只要方法同名同签名一样生效**。实现它的意义是让契约显式（静态分析/调用方一眼看清），不是运行时校验。
5. **实例化方式**：`Command` 走的是 `(new $class)->__commandMeta()`（**会执行构造函数**，不调用单例工厂、不传构造参数），所以别在构造函数里做重活或依赖它被跳过；作为对比，[PermissionMenu](Ext-PermissionMenu.md) 的同类钩子 `__permissionMenuMeta()` 走 `ReflectionClass::newInstanceWithoutConstructor()`，**不执行构造函数**——两者刻意不同（命令类通常是轻量单例，控制器类不应在构菜单时被构造）。

## 方法列表

### 公共方法

    public function __commandMeta(): array
返回本命令类的命令表 `[命令名 => 描述]`；`Command` 收集 CLI 帮助时会整体采用它的返回值。

## 相关链接

- [DuckPhp\Component\Command](Component-Command.md) — 消费方（`getCommandsByClass()` / `getCommandListInfo()`）
- [DuckPhp\Core\Console](Core-Console.md) — 命令执行与注册（`console_command_classes`）
