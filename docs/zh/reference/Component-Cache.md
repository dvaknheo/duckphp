# DuckPhp\Component\Cache

缓存组件抽象/默认空实现：声明一套 PSR-16-like（`Psr\SimpleCache\CacheInterface` 风格注释，非 implements）缓存操作接口的骨架；在不做真实替换时是“什么都不存”的空实现。

## 简介

`Cache extends ComponentBase` 为整条目录的缓存接口提供兜底。直接使用 `Cache::_()` 的行为是 **no-op**（get→default；set/delete/has→false；clear 无操作），目的是让代码既能面向一组稳定缓存 API，又不引入默认不可控存储。

真正缓存能力由具体缓存包装（如 `Component\RedisCache`）或你项目实现的同类接口提供：把它们整体替换成 `Component\Cache` 的位置即可，业务在使用层统一 `Cache接口 + methods`。

可把它视作“缓存入口的契约 + 无缓存降级”。当你需要把 `Cache`（或默认值）在系统里当全局入口，请用 `Manager`/ext 给它配一个真实现。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class Cache extends ComponentBase`（源码注释 `implements Psr\SimpleCache\CacheInterface`——未用关键字 implements）

## 方法列表

> 无自有 options。

### 公共方法

    public function get($key, $default = null)
返回 $default（空实现不存）。

    public function set($key, $value, $ttl = null)
返回 false（未持久）。

    public function delete($key)
返回 false。

    public function has($key)
返回 false。

    public function clear(): void
无内容直接 return（void）。

    public function getMultiple($keys, $default = null)
逐 key 委托 get：仅组装并返回各键默认。

    public function setMultiple($values, $ttl = null)
对每个 {key=>value} 调 set（均 false）；兑现空实现语义返回 true 以保证外层逻辑继续。

    public function deleteMultiple($keys)
基类实现就是 `return $this->delete($keys);`——它把整个键数组当作**一个键**交给 `delete()`（这个桩实现本来什么都不删，所以看不出差别）。要真正批量删除，覆盖本方法。

> 语义：此基类只是契约空壳；加缓存的目标请覆盖这些方法，或改用其他实现。

## 相关链接

- [DuckPhp\Component\RedisCache](Component-RedisCache.md) —— Redis 真实现
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- PSR 参照：`Psr\SimpleCache\CacheInterface`
