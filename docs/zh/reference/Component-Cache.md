# DuckPhp\Component\Cache

缓存组件（占位实现）。

## 简介

`Cache` 组件提供一个缓存接口的占位实现。默认情况下，它不会真正存储任何数据：

- `get()` 始终返回 `$default`
- `set()` 始终返回 `false`
- `has()` 始终返回 `false`
- `delete()` 始终返回 `false`
- `clear()` 不执行任何操作

该组件的主要作用是占住缓存接口的位置，方便后续替换为真实缓存实现（如 RedisCache、Memcached 等）。框架通过该组件在 `Business\Helper` 中提供 `Helper::Cache()` 访问点。

该组件没有公共选项，通过 `Business\Helper::Cache()` 获取当前相位下的单例。

## 选项

`Cache` 组件没有公共选项。

## 使用方式

### 通过 Business Helper

```php
use DuckPhp\Foundation\Business\Helper;

$cache = Helper::Cache();
$cache->set('key', 'value', 3600);
$value = $cache->get('key', 'default');
```

### 直接通过 Cache 组件

```php
use DuckPhp\Component\Cache;

$value = Cache::_()->get('key', 'default');
Cache::_()->set('key', 'value', 3600);
```

## 自定义缓存实现

由于默认 `Cache` 是空实现，通常需要替换为真实缓存。可以通过以下方式扩展：

```php
use DuckPhp\Component\Cache;

class MyCache extends Cache
{
    protected $data = [];
    
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
    
    public function set($key, $value, $ttl = null)
    {
        $this->data[$key] = $value;
        return true;
    }
    
    public function has($key)
    {
        return isset($this->data[$key]);
    }
    
    public function delete($key)
    {
        unset($this->data[$key]);
        return true;
    }
    
    public function clear()
    {
        $this->data = [];
    }
}
```

然后在 `Business\Helper` 或系统初始化中替换单例：

```php
use DuckPhp\Component\Cache;

Cache::_(new MyCache());
```

## 注意事项

1. 默认 `Cache` 组件是空实现，不会持久化任何数据。
2. 生产环境中应替换为 `RedisCache` 或其他真实缓存实现。
3. 该组件支持 PSR-16 风格的接口（`get`/`set`/`delete`/`has`/`clear`/`getMultiple`/`setMultiple`/`deleteMultiple`），但目前未实现 `Psr\SimpleCache\CacheInterface` 接口。

## 方法列表

### 公共方法

    public function get($key, $default = null)
获取缓存值。默认实现始终返回 `$default`。

    public function set($key, $value, $ttl = null)
写入缓存。默认实现始终返回 `false`。

    public function delete($key)
删除缓存键。默认实现始终返回 `false`。

    public function has($key)
检查缓存键是否存在。默认实现始终返回 `false`。

    public function clear()
清空所有缓存。默认实现不执行任何操作。

    public function getMultiple($keys, $default = null)
批量获取缓存值。默认实现对每个键调用 `get()` 并返回结果数组。

    public function setMultiple($values, $ttl = null)
批量写入缓存。默认实现对每个键调用 `set()` 并返回 `true`。

    public function deleteMultiple($keys)
批量删除缓存键。默认实现直接调用 `delete($keys)`。

## 相关链接

- [DuckPhp\Component\RedisCache](Component-RedisCache.md)
- [DuckPhp\Foundation\Business\Helper](Foundation-Business-Helper.md)
