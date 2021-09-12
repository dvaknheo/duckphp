# DuckPhp\Ext\Installer
[toc]

## 简介

Installer 提供了一个安装器类，你可以在这上面扩充。

InstallableTrait 使用来安装 
## 选项
        'install_force' => false,
强制安装

        'install_table_prefix' => '',
安装的表前缀

        'install_sql_dump_options' => [],
sqldump 选项
        'path_install_lock' => 'config',
锁的位置

## 方法



## 说明

    public function __construct()
创建, 设置关联异常为 InstallerException


    public function isInstalled()
是否已经安装

    public function checkInstall()
检查安装，

    public function install($options = [])
安装

    public function dumpSql()
导出 SQL     

    protected function initSqlDumper()
初始化 SqlDumper

    protected function writeLock()
写入锁文件

