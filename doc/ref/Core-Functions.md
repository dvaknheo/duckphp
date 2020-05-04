# Core\RuntimeState

## 简介

`组件类` 保存运行时状态的类
## 选项

## 公开方法

public function __construct()

    空创建方法
public static function ReCreateInstance()

    重新创建实例，避免旧实例状态干扰。
public function isRunning()

    是否在运行状态
public function begin()

    开始
public function end()

    结束运行状态。
public function skipNoticeError()

    跳过 Notice 错误，用于视图显示。 这个函数可能会变更
    
## 详解
