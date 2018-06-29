# DNMVCS
## DNMVCS 是什么
一个 PHP Web 简单框架 
* 主要卖点：比通常的 Model Controller View 多了 Service 。拟补了 常见 Web 框架少的缺层。
这个缺层导致了很糟糕的境地。你会发现很多人在 Contorller 里写一堆代码，或者在 Model 里写一堆代码。
使得网站开发者专注于业务逻辑。

* 为偷懒者写的。只需要引用一个文件，不做一大堆外部依赖。composer 安装正在学习中。
* 小就是性能。
* 替代 Codeiginter 这个PHP4 时代的框架，只限于新工程。
* 不仅仅支持全站路由，还支持局部路径路由和非 path_info 路由

## 关于 Servivce 层
MVC 结构的时候，你们业务逻辑放在哪里？
新手 controller ，后来的放到 model ，后来觉得 model 和数据库混一起太乱， 搞个 Dao 层吧.
所以，Service 按业务走，model 层按数据库走，这就是 DNMVCS 的理念， 还有， 去你的 Dao.
## DNMVCS 使用理念
DNMVCS 的最大意义是思想，只要思想在，什么框架你都可以用
简化的 DNMVC 层级关系图
```
		   /-> View
Controller --> Service ---------------------------------------------------> Model   
					  \                \             /
					   \-> LibService --> ExModel --/
```
* Controller 按 url 入口走 调用 view 和service
* Service 按业务走 ,调用 model 和其他第三方代码。
* Model 按数据库表走，只实现和当前表相关的操作。有些时候
* View 按页面走
* 不建议 Model 抛异常
1. 如果 Service 相互调用怎么办?
添加后缀为 LibService 用于 Service 共享调用，不对外，如MyLibService
2. 如果跨表怎么办?

两种解决方案
1. 在主表里附加
2. 添加后缀为 ExModel 用于表示这个 Model 是多个表的，如 UserExModel。或者单独和数据库不一致如取名 UserAndPlayerRelationModel

## DNMVCS 做了什么
* 简单可扩展灵活的路由方式 => 要不是为了 URL 美化，我才不做这个。
* 简单的数据库类 => 这个现在推荐整合 Medoo 食用
* 扩展接管默认错误处理 => 你也自己处理异常错误
* 简单的配置类  => setting 就是一个数组， config 就是动态配置
* 简单的加载类  => 只满足自己需要.
所有这些仅仅是在主类里耦合。

##  [开始使用](Guide.md)
参考使用文档

## 还有什么要说的

使用它，鼓励我，让我有写下去的动力