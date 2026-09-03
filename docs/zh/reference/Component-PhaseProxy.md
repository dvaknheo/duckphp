# DuckPhp\Component\PhaseProxy

跨 Phase 对象代理：把一个对象包装起来，从而在调用它时自动切换到它所属的 Phase 再执行并复原。

## 简介

`PhaseProxy` 把一个“外部/由非默认 Phase 需要访问”的对象包起来，让调用方法前自动 `App::Phase(目标phase)`、调用后切换回调用前 Phase：

- 构造传入 `$phase` 与 `$overriding`（对象或类名，通常远程 App 别的 instance）；
- `CreatePhaseProxy($phase,$overriding)` 静态便利；phase 空则取当前；
- `__call` 实现前面切 phase、调内部对象方法、恢复；
- `self()` 返回底层对象；`phase()` 读写目标 phase。

典型用途：DuckPhp 用 provider 把 GlobalAdmin/GlobalUser 包成 PhaseProxy，从而跨阶段得到其干净实例执行（例如给用户 provider 指定phase）。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class PhaseProxy`
- 关联：使用 App::Phase 切phase（PhaseContainer）。

## 使用方式

```php
use DuckPhp\Component\PhaseProxy;

// 包装目标对象，phase='myapp' 时跨阶段改期执行
$proxy = PhaseProxy::CreatePhaseProxy('myapp', $someService);
$proxy->doSomething($arg);      // 当前 phase 临时切 myapp 运行再复原
PhaseProxy::self();             // 无 param
$proxy->phase('other');
```

## 注意事项

- overriding 允许是被 `new` 的类名；惰性实例化（首次调用）。
- __call 依赖 `App::Phase(...)`/恢复；要放至调用真的结束。
- 这更多是给 provider 在局部用——业务很少需要自己 new。

## 方法列表

    public function __construct($phase, $overriding)
set phase/overriding。

    public static function CreatePhaseProxy($phase, $overriding)
phase 缺省取当前（App::Phase()），返回 new static.

    protected function getObjectForPhaseProxy(): object
惰性物化 overriding（对象直接用；类名则 `new`）。

    public function __call($method, $args)
切 phase→用底层对象调 method with args →恢复，返回其结果。

    public function self(): object
返回底层对象（触发物化）。

    public function phase($new = null)
读/写目标 phase。

## 相关链接

- [DuckPhp\Core\App Phase](Core-KernelTrait.md)
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)、[DuckPhp\GlobalAdmin\GlobalAdmin]（Provider 也用 PhaseProxy）
