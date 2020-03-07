# 开发者文档

## 代码风格规范
```
php-cs-fixer fix src
```
## 代码检查

```
phpstan analyse -l 7 -a autoload.php src

```
## 测试
DuckPHP 使用phpunit 做单元覆盖测试

```
phpunit
```
单个文件测试
```
phpunit tests/Core/AppTest.php && phpunit tests/support.php
```


基本环境是 wsl .
tests/test_coveragedumps 是保存的 phpunit dump 文件。
tests/test_reports 是输出的报告文件。

这两个在 phphunit 执行的时候要有写入权限。需要进一步判断文件权限，以防初级者不知道

## 文档的生成

tests/genref 可以生成 doc 文档。

```
phpunit tests/genref.php
```