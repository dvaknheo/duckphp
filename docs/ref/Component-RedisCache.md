# DuckPhp\Ext\RedisCache

## 简介
Redis 的 SimpleCache  类
## 选项
全部选项

        'redis_cache_skip_replace' => false,
跳过默认 cache 替换

        'redis_cache_prefix' => '',
Redis Cache 的 key 前缀

## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    public function get($key, $default = null)
    public function set($key, $value, $ttl = null)
    public function delete($key)
    public function has($key)
    public function clear()
    public function getMultiple($keys, $default = null)
    public function setMultiple($values, $ttl = null)
    public function deleteMultiple($keys)
//

    protected function initContext(object $context)
继承 ComponentBase 的initContext 保存对象

    protected function redis()
获取redis 对象的方法，用于重写

### RedisCache
适配 redis 的 psr-16 (注意没实现 psr-16接口)

要和 RedisManager 扩展一起用。。。
