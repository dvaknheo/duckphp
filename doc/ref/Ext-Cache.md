# DuckPhp\Ext\Cache
[toc]

## 简介
空缓存`组件类` psr-16 缓存类(注意没实现 psr-16接口)

## 选项
无选项

## 方法

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

得到的只是空的缓存类，用于其他缓存类接管
