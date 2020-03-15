# Ext\HookChain

## 简介

这是个特殊类。并没有引用
## 方法
    public function __invoke()
    public static function Hook(&$var, $callable, $append = true, $once = true)
    public function add($callable, $append, $once)
    public function remove($callable)
    public function has($callable)
    public function all()
    public function offsetSet($offset, $value)
    public function offsetExists($offset)
    public function offsetUnset($offset)
    public function offsetGet($offset)
## 详解

    public function __construct()
    public function __invoke()
    public static function Hook(&$var, $callable, $append = true, $once = true)
    public function add($callable, $append, $once)
    public function remove($callable)
        $this->chain = array_filter($this->chain, function ($v, $k) use ($callable) {
    public function has($callable)
    public function all()
    public function offsetSet($offset, $value)
    public function offsetExists($offset)
    public function offsetUnset($offset)
    public function offsetGet($offset)