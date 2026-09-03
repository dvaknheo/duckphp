# DuckPhp\Core\Runtime

运行期状态与输出缓冲管理的小组件：标记“正在运行”、捕获是否输出过、`run()/clear()` 兜输出缓冲、进入异常时改状态。

## 简介

`Runtime`（`class Runtime extends ComponentBase`）不是大的业务组件，而是应用生命周期里的一个“状态记录 + output buffer 容器”：

- 提供 `isRunning/isInException/isOutputed` 三个只读状态；
- `run()`：若 opts `use_output_buffer` true，则 `ob_start()` 并记住进入前 ob 层级；
- `clear()`：结束时把自身的缓冲区 flush 到进入时层级，并把 is_running=false、is_outputed=true；
- `onException()`：把 `is_in_exception` 置真（供上层判断此刻是否在异常处理路径上）。

Kernel `serve()`/异常流会围绕它来包“一次请求运行窗口”。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class Runtime extends ComponentBase`

## 选项

`Runtime::$options`

| 选项 | 默认值 | 说明 |
|---|---|---|
| `use_output_buffer` | `false` | 是否开启整体输出缓冲；开启时 run() 开始一个 ob、clear() 负责 flush。 |

## 使用方式

```php
use DuckPhp\Core\Runtime;
$r = Runtime::_()->init(['use_output_buffer'=>true]);
$r->run();          // 若开缓冲 -> ob_start
// ... 输出 ...
$r->clear();        // 缓冲 flush；is_outputed 变 true
Runtime::_()->isRunning();       // false（已清）
Runtime::_()->isInException();
```

## 配置示例

```php
// app options（框架通常按此兜）
Runtime::_()->init([
    'use_output_buffer' => true,   // 需要等“谁先输出”都要由缓冲收拢时开启
]);
```

## 注意事项

- `clear()` 若尚未 `run()` 又没输出层级会直接 false。
- onException 状态会被 Kernel 用来调整：异常期间到底层 handler。
- clear 会 flush 但不会 abandon；需要完全吞可用到别处 ob API。

## 全部选项

```php
    public $options = [
        'use_output_buffer' => false,
    ];
```

## 方法列表

### 公共方法

    public function isRunning()
是否在 run→clear 之间的运行窗口内。

    public function isInException()
是否已走进 onException（异常处理中）。

    public function isOutputed()
本轮是否已 clear/输出结束。

    public function run()
若 use_output_buffer 则记录初始 ob 层级后开始缓冲；置 is_running=true。

    public function clear()
结束时把自身已开缓冲 flush 到 init 层级；is_running=false；is_outputed=true。若未运行返回 false。

    public function onException()
把 is_in_exception 置为 true。

## 相关链接

- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — serve 流程围绕它管理“一次请求的窗口”与异常 onException 状态
