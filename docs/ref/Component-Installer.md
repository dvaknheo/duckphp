# DuckPhp\Componet
[toc]
## 简介

安装器组件

## 选项

'path' => '',
安装路径
'namespace' => '',
安装的命名空间
'force' => false,
强制模式
'autoloader' => 'vendor/autoload.php',
自动加载模式
'verbose' => false,
显示详情
'help' => false,
显示帮助

## 公开方法

### 组件方法

### 其他方法

## 详解

Installer 是辅助安装类。

选项同时作为命令行参数
 
一般不在系统里加载，使用以下命令查看帮助

```
vendor/bin/duckphp new  --help
```