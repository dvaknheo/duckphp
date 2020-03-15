# Ext\FacadesAutoLoader

## 简介

## 选项
        'facades_namespace' => 'Facades',
        'facades_map' => [],
        'facades_enable_autoload' => true,
## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    public function _autoload($class)
    public function getFacadesCallback($input_class, $name)
    public function clear()