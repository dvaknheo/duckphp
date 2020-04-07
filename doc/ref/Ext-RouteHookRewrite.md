# Ext\RouteHookRewrite

## 简介
用于重写URL.

## 选项
rewrite_map=>[]
## 公开方法


## 详解

### RouteHookRewrite
默认开启 实现了rewrite 。

rewrite 支持以 ~ 开始表示的正则， 并且转换后自动拼凑 $_GET
#### 选项
    'rewrite_map'=>[],
#### 方法
assignRewrite()
getRewrites()


    public function __construct()
    public static function Hook($path_info)
    public function init(array $options, object $context = null)
    public function assignRewrite($key, $value = null)
    public function getRewrites()
    public function replaceRegexUrl($input_url, $template_url, $new_url)
    public function replaceNormalUrl($input_url, $template_url, $new_url)
    public function filteRewrite($input_url)
    protected function changeRouteUrl($url)
    protected function doHook($path_info)