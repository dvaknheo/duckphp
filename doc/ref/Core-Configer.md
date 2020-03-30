# Core\Configer

## 简介

## 选项
'path' => '',
'path_config' => 'config',

'setting' => [],
'all_config' => [],
'setting_file' => 'setting',
'skip_setting_file' => false,
'skip_env_file' => true,
## 公开方法

    public function init($options=[], $context=null)
    public function _Setting($key)
    public function _Config($key, $file_basename='config')
    public function _LoadConfig($file_basename='config')
    public function prependConfig($name, $data)
## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    public function _Setting($key)
    public function _Config($key, $file_basename = 'config')
    public function _LoadConfig($file_basename = 'config')
    public function prependConfig($name, $data)
    protected function loadFile($file)
    
    
### Core\Configer

##### 选项

```
    'path'=>null,
    'path_config'=>'config',    //配置路径目录
    'all_config'=>[],
    'setting'=>[],
    'setting_file'=>'setting',
    'skip_setting_file'=>false,
```

##### 说明

Core\Configer 的选项共享个 path,带个 path_config

path_config 如果是 / 开始的，会忽略 path 选项

    当你想把配置目录 放入 app 目录的时候，调整 path_config
    当我们要额外设置，配置的时候，把 setting , all_config 的值 带入
    当我们不需要额外的配置文件的时候  skip_setting_file 设置为 true

##### 方法

### 