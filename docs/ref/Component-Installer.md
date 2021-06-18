# DuckPhp\Component\Installer
[toc]
## 简介

安装器组件

## 选项
全部选项

        'path' => '',
安装路径

        'namespace' => '',
安装的命名空间

        'force' => false,
安装器，强制安装，覆盖现有文件

        'autoloader' => 'vendor/autoload.php',
安装器，自动加载器指向位置

        'verbose' => false,
安装器，显示详情

        'help' => false,
安装器，显示帮助
##  方法

    public static function RunQuickly($options)
    public function init(array $options, $context = null)
    public function run()
通用运行方法

    protected function dumpDir($source, $dest, $force = false)
    
    protected function checkFilesExist($source, $dest, $files)
    
    protected function createDirectories($dest, $files)
    
    protected function filteText($data, $is_in_full, $short_file_name)
    
    protected function filteMacro($data)
    
    protected function filteNamespace($data, $namespace)
    
    protected function changeHeadFile($data, $short_file_name, $autoload_file)
    
    protected function showHelp()

## 说明

Installer 是辅助安装类。

`选项`同时作为`命令行参数`使用

一般不在系统里加载，使用以下命令查看帮助

```
vendor/bin/duckphp new  --help
```


