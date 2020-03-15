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