# 新的开发范式（持续施工中

从 1.2.12 版本开始，DuckPhp 添加了新的开发范式

src 多了个 foundation 目录。

模板样例， 多了 template/public/advance  目录
这就是新版的  开发目录

我们可以看看这个目录结构

```
├── Business
│   ├── BaseBusiness.php
│   ├── BusinessException.php
│   └── DemoBusiness.php
├── Controller
│   ├── Base.php
│   └── Main.php
├── ControllerEx
│   └── Session.php
├── Model
│   ├── BaseModel.php
│   └── DemoModel.php
└── System
    ├── App.php
    ├── ProjectBusiness.php
    ├── ProjectController.php
    ├── ProjectException.php
    ├── ProjectModel.php
    └── ProjectSession.php  // session
```
可以看到少了 Helper 目录，多了  ControllerEx 目录， System 目录多了好几个 Project 开头的文件

那么 Foundation 的体现在哪里呢？
在此之前我们看一下 Foundation 有什么文件

```
Installer.php 
SessionManagerBase.php  // Session 的前缀
SimpleControllerTrait.php  // 
SimpleModelTrait.php
SqlDumper.php
ThrowOnableTrait.php

```

新模式要点：

1. 除了 System 目录，其他目录禁止和 DuckPhp 命名空间有联系
2. 其他目录，尽量少的和 System 目录联系
3. 原先 Helper 都缩进 Base/BaseBusiness/BaseModel


世界是复杂的 
1. ViewHelper 因为 View 里不引用命名空间，所以改用全局函数
2. ModelHelper 就几个函数，所以并入. Model 这一层，折腾的是在多模型关联
3. Business 这一层， Business 也是没几个函数，所以并入。 Business 的问题是在于不同异常。 所以 ThrowOnableTrait 省事了
4. Controller 这一层，最大的问题是太多东西了，比如引入第三方的东西， 所以 ControllerHelper 类没法解决， 我们抽出 ControllerEx 目录来解决
5. 所以我们就有了 session 这个类。 但是 其他动作，我们用 Action 后缀吧。



