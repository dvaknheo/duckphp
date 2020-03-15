# Ext\Pager

## 简介
分页类
组件类
## 选项
        'url' => null,
        'current' => null,
        'page_size' => 30,
        'page_key' => 'page',
        'rewrite' => null,
## 公开方法


## 详解

    public function __construct()
    public static function SG()
    public function _SG()
    public function init(array $options, object $context = null)
    public function current()
    public function pageSize($new_value = null)
    public function getPageCount($total)
    public function getUrl($page)
    public function defaultGetUrl($page)
    public function render($total, $options = [])