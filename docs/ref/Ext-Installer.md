# DuckPhp\Foundation\Installer
[toc]

## 简介

Installer 提供了一个安装器类，你可以在这上面扩充。
## 选项

## 方法



## 说明

1.2.12 版本尚未完成功能
        'install_lock_file' => '???',

        'force' => false,

    public function __construct()

    protected function checkInstall()

    public function install($parameters)

    public function init(array $options, ?object $context = null)

    public function isInstalled()

    protected function writeLock()

    public function run()

    public function export()

    protected function getComponenetPath($path, $basepath = ''): string

