# DuckPhp\Foundation\Installer
[toc]

## 简介

Installer 提供了一个安装器类，你可以在这上面扩充。
## 选项
        'install_force' => false,

        'install_table_prefix' => '',

        'install_sql_dump_options' => [],

        'path_install' => 'config',
安装的目录

## 方法



## 说明

1.2.13 版本完成功能




    public function checkInstall()

    public function install($options = [])

    public function dumpSql()

    protected function initSqlDumper()

    public function __construct()

    public function isInstalled()

    protected function writeLock()


