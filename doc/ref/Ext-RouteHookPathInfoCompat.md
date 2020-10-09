# DuckPhp\Ext\RouteHookPathInfoCompat

## 简介
`默认``组件类` 在不配置 PathInfo 下模拟
## 选项

'path_info_compact_enable' => false,

    注意，这项开启扩展才启用。

'key_for_action' => '_r',

    替代的 action
'key_for_module' => '',
    
## 公开方法
public function __construct()
public function init(array $options, object $context = null)
public static function URL($url = null)
public function onURL($url = null)
public static function Hook($path_info)
public function _Hook($path_info)
protected function filteRewrite($url, &$ret = false)

## 详解

    