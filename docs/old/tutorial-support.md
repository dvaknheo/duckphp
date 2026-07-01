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
./vendor/bin/php-cs-fixer fix && ./vendor/bin/phpstan analyse && ./vendor/bin/phpunit

```
五连
```
./vendor/bin/php-cs-fixer fix && ./vendor/bin/phpstan analyse && ./vendor/bin/phpunit && php tests/genoptions.php && dot docs/duckphp.gv -T svg -O

```
