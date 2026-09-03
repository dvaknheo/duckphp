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
- `command_routes()` 让 RouteLister 枚举路由并带高亮打印
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

help 用 `Command::command_help()` 里收集 `Console` 各注册类的方法名（可选 per class `getCommandsOfThis`），逐个从 `@command_desc`/doc 首行取描述；命名空间为空的是 `*Default*`。

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

    public function command_routes(bool $with_children = true, bool $only_controller = false, bool $only_admin = false, bool $only_user = false): void
交给 RouteLister listAll() 后端颜色高亮输出 url/controller/route-map/admin-user/phase。

    public function command_debug(bool $off = false): void
开关调试标记（写 ext options 之 is_debug），受限前提：data_file_enable + data_file_bump_allowed…

### 公共方法（供类扩展/描述）

    public function getCommandsOfThis($method_prefix, $phase)
若某命令类自己提供此方法，框架用反射类方法名提取命令列表。

### 受保护方法

    protected function getCommandListInfo(): string
历遍 Console classes，打印每个 namespace（默认 `*Default*`）->组命令（去前缀+pad）。

    protected function getCommandsByClasses(array $classes, string $method_prefix, string $phase): array
把多 class 映射并（filter false）聚合各自命令。

    protected function getCommandsByClass(string $class, string $method_prefix, string $phase): array
单个命令类：若有 getCommandsOfThis 则走；否则调用反射提取。

    protected function getCommandsByClassReflection(\ReflectionClass $ref, string $method_prefix): array
反射全部方法，前缀过滤出 command 名，取 @command_desc（或 doc 首行）作为描述，并翻译。

    protected function translateCommandDesc(string $desc): string
把描述里 `[[key|fallback]]` 片段经 `App::langText()` 翻译。

## 相关链接

- [DuckPhp\Core\Console](Core-Console.md) —— 命令执行主机
- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) —— command_run 起服务
- [DuckPhp\Component\RouteLister](Component-RouteLister.md) —— command_routes 用
- [DuckPhp\Core\App](Core-App.md)/langText —— 版本/lang 翻译来源
