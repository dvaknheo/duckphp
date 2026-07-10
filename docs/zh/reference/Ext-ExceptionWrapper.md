# DuckPhp\Ext\ExceptionWrapper

异常包装扩展组件。

## 简介

`ExceptionWrapper` 用于把可变单例调用封装起来，使方法调用在异常时返回异常对象本身，而不是抛出异常。

典型用法：

```php
$ret = ExceptionWrapper::Wrap(MyClass::_())->foo();
```

- 如果 `MyClass::_()->foo()` 正常执行，返回其结果。
- 如果 `foo()` 抛出 `\Exception`，则返回该异常对象。

## 选项

无。本组件不定义配置选项。

## 使用方式

### 包装对象并调用方法

```php
use DuckPhp\Ext\ExceptionWrapper;
use DuckPhp\Component\DbManager;

$ret = ExceptionWrapper::Wrap(DbManager::_())->Db();

if ($ret instanceof \Exception) {
    // 出现异常
    echo $ret->getMessage();
} else {
    // 正常结果
    $db = $ret;
}
```

### 链式调用

```php
$ret = ExceptionWrapper::Wrap(MyService::_())->getUser(1);

if ($ret instanceof \Exception) {
    // 处理异常
} else {
    $user = $ret;
}
```

### 释放包装对象

```php
$wrapper = ExceptionWrapper::Wrap(MyService::_());
$wrapper->doSomething();

$object = ExceptionWrapper::Release(); // 或 $wrapper->doRelease()
```

## 注意事项

1. 只捕获 `\Exception` 及其子类，`\Error` 和 `\Throwable` 不会被捕获。
2. 返回异常对象后，调用方需要自行判断返回值类型。
3. 该组件主要用于需要把异常转换为返回值的场景，例如某些链式调用或测试代码。
4. 在正式业务中，建议显式处理异常，而不是依赖返回异常对象。

## 方法列表

### 公共方法

    public static function Wrap($object)
包装一个对象，返回 ExceptionWrapper 实例

    public static function Release()
释放并返回当前包装的对象

    public function doWrap($object): self
实例方法：设置被包装对象

    public function doRelease(): ?object
实例方法：释放被包装对象

### 魔术方法

    public function __call(string $method, array $args)
代理被包装对象的方法调用，捕获 `\Exception` 并返回异常对象

## 相关链接

- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md)
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
