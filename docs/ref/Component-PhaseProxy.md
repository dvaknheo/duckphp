# DuckPhp\Component\PhaseProxy
[toc]
## 简介

相位代理，用于跨相位调用。

## 选项

无

## 方法

    protected function createObjectForPhaseProxy()

    public function __call($method, $args)

    public function __construct($container_class, $overriding)

    public static function CreatePhaseProxy($container_class, $overriding)

## 说明

因为相位隔离作用
子应用A 想调用 子应用B 的 Something 类。 只能通过代理类来

最常见的是 `Something::CallInPhase(ChildApp::class)->foo();`
内部就是`Something::CallInPhase(ChildApp::class)` 生成代理类，然后代理类 找出当前 Something 类的单例 执行 `foo()` 之后切回来。

$overring 还可以是 $object ，  这样调用的时候，就不是找单例而是从 $object 了。

## 完毕
