# DuckPhp\Ext\RouteHookRewrite

## 简介
用于重写URL.

## 选项
全部选项

        'rewrite_map' => [],
路由重写，重写映射表

        'rewrite_auto_extend_method' => true,
路由重写，自动扩展方法

    选项
## 方法
    public static function Hook($path_info)
    
    protected function initOptions(array $options)
    
    protected function initContext(object $context)
    
    public function assignRewrite($key, $value = null)
    
    public function getRewrites()
    
    public function replaceRegexUrl($input_url, $template_url, $new_url)
    
    public function replaceNormalUrl($input_url, $template_url, $new_url)
    
    public function filteRewrite($input_url)
    
    protected function changeRouteUrl($url)
    
    protected function doHook($path_info)


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


