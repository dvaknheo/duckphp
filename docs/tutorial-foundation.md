# Foundation 基础类
DuckPhp\Foundation 命名空间底下的，是普通工程师要看的内容
```
.
├── Business
│   └── Helper.php
├── CommonCommandTrait.php
├── Controller
│   └── Helper.php
├── ExceptionReporterTrait.php
├── FastInstallerTrait.php
├── Helper.php
├── Model
│   └── Helper.php
├── SimpleBusinessTrait.php
├── SimpleControllerTrait.php
├── SimpleExceptionTrait.php
├── SimpleModelTrait.php
├── SimpleSessionTrait.php
├── SimpleSingletonTrait.php
└── System
    └── Helper.php

```
归类如下

### 常规事物
实现 `_()`可变单例方法和 `ZCall` 跨相位调用方法

SimpleBusinessTrait
业务类，服务类调用。
SimpleControllerTrait.php

`_()`可变单例方法，为了兼容控制器调用做了调整

SimpleModelTrait
额外的，还多加了一些常用方法方便使用。

### 高级

SimpleSessionTrait.php
会话处理，集合在这里。



Session 类， 给不同 应用 加 session_prefix 

SimpleExceptionTrait.php -> ThrowOnTrait
ThrowOn 静态方法
ExceptionReporterTrait
用于接管错误处理


### 命令行相关
CommonCommandTrait -> DuckPhp\Component\CommandTrait
FastInstallerTrait -> DuckPhp\FastInstaller\FastInstallerTrait

### 助手:
DuckPhp\Foundation\Business\Helper  ->
DuckPhp\Foundation\Controller\Helper ->
DuckPhp\Foundation\Model\Helper  ->
DuckPhp\Foundation\System\Helper ->
DuckPhp\Foundation\Helper

DuckPhp\Foundation\Helper 是包含所有的Helper.



