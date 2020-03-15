# Ext\RedisSimpleCache

## 简介
Redis SimpleCache  类
## 选项
        $this->redis = $options['redis'] ?? null;
        $this->prefix = $options['prefix'] ?? '';
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