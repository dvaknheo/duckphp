# DuckPhp\Component\Command

框架内置的一组实用 CLI 命令集合：版本/帮助/内嵌 HTTP 服务/抓取路由/调用方法/列表路由/开关调试。

## 简介

`Command extends ComponentBase` 是 DuckPHP 默认注册的命令包（DuckPhp/DuckPhpAllInOne 会在必要时把它并进当前 App 命令空间，见其 onPrepare）。当你在 CLI 跑 `command help` 等，多是由这里的方法响应。

命令命名法：命令类里以 `command_xxx` 开头的方法就是一个动作；Command 里提供：

- `command_version()` 版本
- `command_help()` 展示使用与命令列表（从 Console 注册与各方法 @command_desc 汇总）
- `command_run()` 用 HttpServer 把当前应用跑成内嵌 HTTP 会话；
- `command_fetch($uri,$post)` 于 CLI 抓取私有路径（经 SuperGlobal context 写入并调 App→serve）
- `command_call(<class>@<method>…)` 调用某个业务方法（business 便利）
- `command_debug($off=false)` 切换 dev flag（需 data_file 能力）

此外含若干被引用的获取/解析辅助方法（methods 列表详下）。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class Command extends ComponentBase`

## 使用方式

（通常已被注册为默认命令，可直接）在 CLI：

```text
php xx run                 # 由 command_run 起内嵌 http
php xx version
php xx help
php xx routes
php xx call foo/MyBiz@doWork a --x=1
php xx fetch '/account/detail'
php xx debug --off
```

命令描述语料（如 help 中 README 文案）来自方法 docblock 里 `@command_desc 语句`（支持 `[[lang|fallback]]`，会经 `translateCommandDesc/langText` 翻译）。

## 实现说明（命令描述从哪里来）

help 用 `Command::command_help()` 里收集 `Console` 各注册类的方法名（可选 per class `__commandMeta()`），逐个从 `@command_desc`/doc 首行取描述；命名空间为空的是 `*Default*`。

## 注意事项

1. 命令收集是**按类独立**的：某命令类只要定义了 `__commandMeta()`（见 [CommandMetaInterface](Component-CommandMetaInterface.md)），其返回值就整体接管该类的命令表——`console_command_classes` 里给它配的“方法前缀”不再生效（该方法内部固定用 `command_` 前缀）。
2. `getCommandsByClasses()` 对 `console_command_classes` 取值形态的处理是**对上游 `Console` 的防御性对齐**（与 `Console` 执行命令时的取法逐条一致）：`false` 或 `null`/未设值 → 跳过；`true` → 用默认前缀 `command_`；字符串 → 该串即前缀。不是本类自创的语义，见 [Core-Console](Core-Console.md) 的 `console_command_classes` 说明。

## 方法列表

### 公共方法（命令）

    public function command_version(): void
输出所属 App::version()。

    public function command_help(): void
输出版本 + 常用 help 文案 + 全部命令列表（getCommandListInfo）。

    public function command_run()
读取 Console CLI 参数，用 HttpServer 使 App 以内嵌 http-server 跑起来（会临时切 cli_enable off）。

    public function command_fetch($uri = '', $post = false)
CLI 里“抓取”：向 __SUPERGLOBAL_CONTEXT (或全局) 写 REQUEST_URI/PATH_INFO/METHOD，再 context->serve()。

    public function command_call()
`namespace/Business@method +args`：解析业务类并反射调用（经 Console::callObject）。


    public function command_debug(bool $off = false): void
开关调试标记（写 ext options 之 is_debug），受限前提：data_file_enable + data_file_bump_allowed…

### 公共方法（供类扩展/描述）

    public function __commandMeta()
以 `command_` 前缀反射本类方法，返回 `[命令名 => 描述]`；命令类只要定义此方法（即实现 [CommandMetaInterface](Component-CommandMetaInterface.md)），`getCommandsByClass()` 就直接采用它的返回值（不再走传入的前缀与反射）。

### 受保护方法

    protected function getCommandListInfo(): string
历遍 Console classes，打印每个 namespace（默认 `*Default*`）->组命令（去前缀+pad）。

    protected function getCommandsByClasses(array $classes): array
把多 class 映射聚合成命令表：值为 `false` 跳过、为 `true` 视作 `command_`、其余按值当前缀（不再接收 phase 参数）。

    protected function getCommandsByClass(string $class, string $method_prefix): array
单个命令类：若该类定义了 `__commandMeta()` 则直接调用它；否则按 `$method_prefix` 反射提取。

    protected function getCommandsByClassReflection(\ReflectionClass $ref, string $method_prefix): array
反射全部方法，前缀过滤出 command 名，取 @command_desc（或 doc 首行）作为描述，并翻译。

    protected function translateCommandDesc(string $desc): string
把描述里 `[[key|fallback]]` 片段经 `App::langText()` 翻译。

## 相关链接

- [DuckPhp\Core\Console](Core-Console.md) —— 命令执行主机
- [DuckPhp\Component\CommandMetaInterface](Component-CommandMetaInterface.md) —— 命令表元数据接口（`__commandMeta()`）
- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) —— command_run 起服务
- [DuckPhp\Ext\RouteLister](Ext-RouteLister.md) —— `command_routes` 命令的实际实现（已从本类迁到那里）
- [DuckPhp\Core\App](Core-App.md)/langText —— 版本/lang 翻译来源
