# DuckPhp\Component\RedisCache

Redis 缓存实现（契合简单 Cache 握手：set 会 JSON UNSICII、set 可选 TTL），缺省可把自己替换成全局 `Cache` 的实际实现。

## 简介

`RedisCache extends ComponentBase`（注释 align `Psr\SimpleCache`）是在 Redis 上实现简单缓存字面入口组件：

- 复用 `RedisManager::Redis()` 连接；
- key 会用 `redis_cache_prefix` 前缀避免跨 app 串扰；
- 值 JSON 编码（UNESCAPED_UNICODE）+ json_decode 取出；
- 提供 get/set/delete/has/…multiple…、clear(未实现/占位)；
- 其构造函数/init 时（除非 `redis_cache_skip_replace`）会 `Cache::_($this)` 向父 Cache 替换引用——即默认组件拿到“缓存”指的就是这个 Redis 实现；否则用基类 Cache 空实现 or 上层替换。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class RedisCache extends ComponentBase`
- 关联连接：RedisManager::Redis()。

## 选项

`RedisCache::$options`：

| 选项 | 默认 | 说明 |
|---|---|---|
| `redis_cache_skip_replace` | false | 若 true 不 replace 全局 Cache（由自己取舍）。 |
| `redis_cache_prefix` | `''` | 本缓存键前缀。 |

## 使用方式

经 DuckPhp 提供的缓存（RedisCache 若启用则是实现）：

```php
use DuckPhp\Component\RedisCache;
use DuckPhp\Component\Cache;

RedisCache::_()->init([...]);      // 缺省 skip=false → Cache::_(this)
Cache::_()->set('k','v');
$ret = Cache::_()->get('k');
```

## 注意事项

- value 存为 JSON 字符串；数字会 json 化出来，取数据按数组用即可重现结构。
- clear 目前只是空实现（return）——真实需要清库自行扩展。
- delete 接受数组（del 多条）。

## 方法列表

### 公共方法

    public function get($key, $default = null)
get 原始 → json decode（否则返回 default / 处理 false boolean？若原始数值 false decode 难——实现获取再 json_decode或 false else）。

    public function set($key, $value, $ttl = null)
json 编码 + redis set（TTL 可选）。

    public function delete($key)
（原数组 key为默认 handled）del。

    public function has($key)
redis exists。

    public function clear()
未实现（占位 return）。

    public function getMultiple($keys, $default = null)
逐 key get 组装。

    public function setMultiple($values, $ttl = null)
循环 set。

    public function deleteMultiple($keys)
delete 数组。

### 受保护方法

    protected function initContext(object $context): void
（skip=false）则 Cache::_($this) 替换框架默认缓存实现。

    protected function redis(): \Redis
由 RedisManager::Redis() 取的连接。

## 相关链接

- [DuckPhp\Component\Cache](Component-Cache.md)
- [DuckPhp\Component\RedisManager](Component-RedisManager.md) —— Redis 连接管理
- Redis cache 若不想全局替换可置 skip。
