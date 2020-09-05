# DuckPhp\Core\RuntimeState
[toc]

## 简介

运行时组件，保存运行状态信息

## 选项

'use_output_buffer' => false,

启用 ob 函数保持

## 公开方法

public static function ReCreateInstance()

    重新创建实例，避免旧实例状态干扰。
public function isRunning()

    是否在运行状态
public function run()

    开始
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

Runtime 类在运行期才重新初始化。

