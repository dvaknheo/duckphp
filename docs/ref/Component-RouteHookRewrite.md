# DuckPhp\Ext\RouteHookRewrite

## 简介
用于重写URL.

## 选项
全部选项

        'rewrite_map' => [],
路由重写，重写映射表

        'controller_url_prefix' => '',
控制器前缀

## 方法

    public static function Hook($path_info)
    protected function doHook($path_info)
路由钩子处理

    protected function initOptions(array $options)
    protected function initContext(object $context)
初始化处理

    public function assignRewrite($key, $value = null)
分配

    public function getRewrites()
    
    public function replaceRegexUrl($input_url, $template_url, $new_url)
    
    public function replaceNormalUrl($input_url, $template_url, $new_url)
    
    public function filteRewrite($input_url)

    protected function changeRouteUrl($url)
调整路由


## 详解

### RouteHookRewrite
默认开启 实现了rewrite 。

rewrite 支持以 ~ 开始表示的正则， 并且转换后自动拼凑 $_GET



assignRewrite($old_url,$new_url=null)

    支持单个 assign($key,$value) 和多个 assign($assoc)
    rewrite  重写 path_info
    不区分 request method , 重写后可以用 ? query 参数
    ~ 开始表示是正则 ,为了简单用 / 代替普通正则的 \/
    替换的url ，用 $1 $2 表示参数
