# 开发者文档
[toc]
本章是用于参与开发者看的

## 代码风格规范

```
./vendor/bin/php-cs-fixer fix src
```
## 代码检查

```
./vendor/bin/phpstan analyse
```
## 测试
DuckPhp 使用phpunit 做单元覆盖测试

```
./vendor/bin/phpunit
```
单个文件测试
```
./vendor/bin/phpunit tests/Core/AppTest.php && ./vendor/bin/phpunit tests/support.php
```
代码风格检查加代码检查加单元测试


```
./vendor/bin/php-cs-fixer fix && ./vendor/bin/phpstan analyse && ./vendor/bin/phpunit

```
基本环境是 wsl .
`test_coveragedumps` 是保存的 phpunit dump 文件。
`test_reports` 是输出的报告文件。

这两个在 phphunit 执行的时候要有写入权限。需要进一步判断文件权限，以防初级者不知道


## 文档例子，选项重新的生成


```
php tests/genoptions.php
```
svg 架构图形生成
```
dot docs/duckphp.gv -T svg -O
```
合并起来，需要手工性的重新合并
```
php tests/genoptions.php && dot docs/duckphp.gv -T svg -O

```
重申
--
三连
```
php-cs-fixer fix && phpstan analyse && phpunit

```
五连
```
php-cs-fixer fix && phpstan analyse && phpunit && php tests/genoptions.php && dot docs/duckphp.gv -T svg -O

```

Q _()方法是不是糟糕了

你可以把 ::_()-> 看成和 facades 类似的门面方法。
可变单例是 DuckPhp 的核心。
你如果引入第三方包的时候，不满意默认实现，可以通过可变单例来替换他

var_dump(MyClass::——()); 使用 Facades 就没法做到这个功能。

Q 为什么不直接用 DB 类，而是用 DbManager

A 做日志之类的处理用

Q 为什么名字要以 *Model *Business *Controller 结尾
让单词独一无二，便于搜索

Q 为什么是 Db 而不是 DB 。
A 为了统一起来。  缩写都驼峰而非全大写

Q 回调 Class::Method Class@Method Class->Method 的区别

A
-> 表示 new 一个实例
@ 表示 $class::_()->

:: 表示 Class::Method

~ => 扩充到当前命名空间

/////////////////

#### 概念速读

门面， DuckPhp 用可变单例代替了门面
中间件， DuckPhp 提供了简单的中间件扩展 MyMiddlewareManager，但不建议使用。

事件，见事件这一篇
 
请求和响应， DuckPhp 没这个概念 但在 控制器助手类里有很多相同的行为

数据库 ，DuckPhp 的数据库没那么强大

模型 

视图 DuckPhp 的视图原则

错误处理
日志  __logger() 得到 psr 日志类， Logger 类

验证， DuckPhp 没验证处理，你需要第三方类

缓存  Cache()

Session  集中化管理

Cookie  集中化管理

多语言 重写 __l 函数

上传 无特殊的上传

命令行  见命令行的教程，和 DuckPhp\Core\Console 参考类

扩展库



