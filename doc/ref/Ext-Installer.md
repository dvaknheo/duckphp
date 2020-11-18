# DuckPhp\Ext\Console
[toc]
## 简介
`组件类` 安装类
## 选项

'path' => '',

    默认路径
'namespace' => '',

    默认命名空间
'force' => false,

    是否强制模式
'autoloader' => 'vendor/autoload.php',

    加载器位置
'verbose' => false,

    显示详情
'help' => false,

    只是显示帮助
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