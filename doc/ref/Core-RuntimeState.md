# Core\RuntimeState

## 简介

`组件类` 保存运行时状态的类
## 选项

## 公开方法

public function __construct()

    空创建方法
public static function ReCreateInstance()

    这个静态函数
public function isRunning()

    是否在运行状态
public function begin()

    开始
public function end()

    运行
public function skipNoticeError()

    跳过 Notice 错误，用于视图显示。
## 详解

    public function __construct()
    public function isRunning()
    public static function ReCreateInstance()
    public function begin()
    public function end()
    public function skipNoticeError()