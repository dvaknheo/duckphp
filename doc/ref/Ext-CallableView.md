# Ext\CallableView

## 简介
CallableView 扩展用于用函数替代文件方式显示视图
## 选项
'callable_view_head' => null,
'callable_view_foot' => null,
'callable_view_class' => null,
'callable_view_prefix' => null,
'callable_view_skip_replace' => false,
'path' => '',
'path_view' => 'view',
## 公开方法


## 详解

    public function init(array $options, object $context = null)
    protected function viewToCallback($func)
    public function _Show($data = [], $view)
    public function _ShowBlock($view, $data = null)
    
    

##### 选项
```php
    'callable_view_head'=>null,  $options=[
   //  页眉函数
    'callable_view_foot'=>null,     //  页脚函数
    'callable_view_class'=>null,    //  限定于某类
    'callable_view_prefix'=>null,   //  前缀
    'callable_view_skip_replace'=>false,    // 初始化的时候替换默认的 Core\View
];
```
所有回调都在 都会限定于 callable_view_class 内，callable_view_class 可以为 object;如果 callable_view_class 为 null 则为全局函数
callable_view_prefix 是方法前缀。 方法名都会把view 的 / 替换成 _
callable_view_skip_replace 打开的时候会在 初始化的时候替换默认的 Core\View
