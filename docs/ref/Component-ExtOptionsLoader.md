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
    public function installWithExtOptions($class, $options)

    public function loadExtOptions()

### 内部方法

    protected function get_ext_options_file()

    protected function get_all_ext_options($force = false)

## 说明

DuckPhp ，根据 'ext_options_file_enable' 是否要加载 ExtOptionsLoader ，加载后 loadExtOptions()
install 的时候，调用 installWithExtOptions（static::class, $options) 保存当前选项


## 完毕
