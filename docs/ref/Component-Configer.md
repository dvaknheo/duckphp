# DuckPhp\Component\Configer
[toc]

## 简介

配置组件 ，DuckPhp 类使用到，在 init 的时候

## 选项
全部选项

        'path' => '',
基本路径

        'path_config' => 'config',
配置路径

## 方法
### 公开方法

    public function _Config($file_basename = 'config', $key = null, $default = null)
读取一个配置

### 内部方法

    protected function _LoadConfig($file_basename)
载入一个配置文件

    protected function loadFile($file)
加载文件
### 说明

DuckPhp\Component\Configer 的选项共享个 path,带个 path_config

path_config 如果是 / 开始的，会忽略 path 选项

覆盖默配置。。。


