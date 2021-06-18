# DuckPhp\Core\Configer
[toc]

## 简介

配置组件

## 选项
全部选项

        'path' => '',
基本路径

        'path_config' => 'config',
配置路径

        'setting' => [],
默认自带的设置

        'all_config' => [],
默认自带的所有配置

        'setting_file' => 'setting',
设置文件名。

        'use_setting_file' => false,
使用设置文件: $path/$path_config/$setting_file.php

        'use_env_file' => false,
使用 .env 文件
打开这项，可以读取 path 选项下的 env 文件

        'config_ext_file_map' => [],
额外的配置文件数组，用于 AppPluginTrait

        'setting_file_ignore_exists' => false,
如果设置文件不存在也不报错

## 方法
### 公开方法

    public function _Config($key, $file_basename = 'config')
读取一个配置

    public function _LoadConfig($file_basename = 'config')
载入一个配置文件

    public function _Setting($key)
读取设置

### 内部方法

    protected function initOptions(array $options)
重写了初始化选项函数

    protected function exitWhenNoSettingFile($full_setting_file, $setting_file)
用于重写，没设置文件则退出

    protected function loadFile($file)
用于重写，载入文件

    private function exitWhenNoSettingFile($full_setting_file, $setting_file)
用于重写，如果没设置文件的时候报错

### 说明

DuckPhp\Core\Configer 的选项共享个 path,带个 path_config

path_config 如果是 / 开始的，会忽略 path 选项

当你想把配置目录 放入 app 目录的时候，调整 path_config

当我们要额外设置，配置的时候，把 setting , all_config 的值 带入

当我们需要额外的配置文件的时候  use_setting_file 设置为 true

基于  AppPluginTrait  需要， Configer 类比普通类多了 config_ext_files 选项

`setting_file_ignore_exists` 为了方便安装程序
