# DuckPhp\Component\ExtOptionsLoader
[toc]
## 简介

额外选项加载组件 

## 选项

无选项，但被 App 的选项影响

        'ext_options_file_enable' => false,
//启用
        'ext_options_file' => 'config/DuckPhpApps.config.php',

## 方法

### 公开方法
    public function saveExtOptions($options, $class = null)

    public function loadExtOptions($force = false, $class = null)

### 内部方法

    protected function get_ext_options_file()

    protected function get_all_ext_options($force = false)

    protected function saveExtOptions($class, $options)

## 说明

DuckPhp ，根据 'ext_options_file_enable' 是否要加载 ExtOptionsLoader ，加载后 loadExtOptions()
install 的时候，调用 installWithExtOptions（static::class, $options) 保存当前选项

Core 组件的初始化在 此之前完成，所以不会被重载。

## 完毕



