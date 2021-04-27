# DuckPhp\Component\Cache
[toc]

## 简介
psr-16 缓存`组件类`(注意没实现 psr-16接口)

## 选项

无选项

## 方法

和 psr-16 接口一致

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

## 详解

得到的只是空的缓存类，用于其他缓存类接管。 因为第三方很可能用到 缓存之类