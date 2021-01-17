# 开发者文档
[toc]
本章是用于参与开发者看的

## 代码风格规范

```
php-cs-fixer fix src
```
## 代码检查

```
phpstan analyse

```
## 测试
DuckPhp 使用phpunit 做单元覆盖测试

```
phpunit
```
单个文件测试
```
phpunit tests/Core/AppTest.php && phpunit tests/support.php
```
代码风格检查加代码检查加单元测试


```
php-cs-fixer fix && phpstan analyse && phpunit

```
基本环境是 wsl .
tests/test_coveragedumps 是保存的 phpunit dump 文件。
tests/test_reports 是输出的报告文件。

这两个在 phphunit 执行的时候要有写入权限。需要进一步判断文件权限，以防初级者不知道
相关版本
php-cs-fixer 2.16.4 
phpstan 0.12.35
phpunit 9.2.0  // 不是 phpunit 9.3 版哦

## 文档例子，选项重新的生成


```
php tests/genoptions.php
```
svg 架构图形生成
```
dot doc/duckphp.gv -T svg -O
```
合并起来，需要手工性的重新合并
```
php tests/genoptions.php && dot doc/duckphp.gv -T svg -O

```
重申
--
三连
```
php-cs-fixer fix && phpstan analyse && phpunit

```
五连
```
php-cs-fixer fix && phpstan analyse && phpunit && php tests/genoptions.php && dot doc/duckphp.gv -T svg -O

```







Helper 类为什么要在 Helper 目录下，

原因，配合 cloneHelper 用。

System 目录下，为什么以 Base 开头。

因为一开始是 Base 目录

为什么会有个“我觉得恶心的”G() 单字母静态方法

你可以把 ::G()-> 看成和 facades 类似的门面方法。
可变单例是 DuckPhp 的核心。
你如果引入第三方包的时候，不满意默认实现，可以通过可变单例来替换他

var_dump(MyClass::G()); 使用 Facades 就没法做到这个功能。


为什么不直接用 DB 类，而是用 DbManager
做日志之类的处理用

为什么名字要以 *Model *Business 结尾
让单词独一无二，便于搜索

为什么是 Db 而不是 DB 。
为了统一起来。  缩写都驼峰而非全大写