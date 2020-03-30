# Core\SuperGlobal

## 简介
超级变量类，本类不带选项
RuntimeState 类用于保存运行时数据。无配置

## 公开方法
    public function __construct()
    public function init(array $options, object $context = null)
    public function session_start(array $options=[])
    public function session_id($session_id)
    public function session_destroy()
    public function session_set_save_handler($handler)
    public function &_GLOBALS($k, $v=null)
    public function &_STATICS($name, $value=null, $parent=0)
    public function &_CLASS_STATICS($class_name, $var_name)

## 详解

和组件类不同， Super
    public function __construct()
    public function reset()
    public function init(array $options, object $context = null)
    public function session_start(array $options = [])
    public function session_id($session_id)
    public function session_destroy()
    public function session_set_save_handler($handler)
    public function &_GLOBALS($k, $v = null)
    public function &_STATICS($name, $value = null, $parent = 0)
    public function &_CLASS_STATICS($class_name, $var_name)