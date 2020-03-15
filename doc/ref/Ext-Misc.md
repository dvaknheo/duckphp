# Ext\Misc

## 简介
杂项扩展
## 选项
    public $options = [
        'path' => '',
        'path_lib' => 'lib',
    ];
## 公开方法


## 详解

    public function init(array $options, object $context = null)
    
    public function __construct()
    public function init(array $options, object $context = null)
    public static function Import($file)
    public static function RecordsetUrl($data, $cols_map = [])
    public static function RecordsetH($data, $cols = [])
    public static function DI($name, $object = null)
    public function CallAPI($class, $method, $input, $interface = '')
    public function _DI($name, $object = null)
    public function _Import($file)
    public function _RecordsetUrl($data, $cols_map = [])
    public function _RecordsetH($data, $cols = [])
    public function _CallAPI($class, $method, $input, $interface = '')