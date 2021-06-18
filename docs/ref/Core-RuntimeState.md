# DuckPhp\Core\RuntimeState
[toc]

## 简介

运行时组件，保存运行状态信息

## 选项
只有一个选项

        'use_output_buffer' => false,
使用 OB 函数缓冲数据

## 方法

    public function reset()
重置，新创建实例，避免旧实例的状态干扰。

    public function run()
运行

    public function clear()
清理

    public function isRunning()
是否运行。

    public function toggleInException($flag = true)
设置在异常状态

    public function isInException()
是否已经异常

    public function isOutputed()
是否已经输出

    public function toggleOutputed($flag = true)
设置已经输出

## 详解

运行时组件，保存运行状态信息。

Runtime 类在运行期才重新初始化。

