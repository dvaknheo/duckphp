# Ext\DBReusePoolProxy

## 简介

## 选项
        'db_reuse_size' => 100,
        'db_reuse_timeout' => 5,
## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    protected function initContext($options = [], $context = null)
    protected function getObjectIndex($tag)
    protected function getObjectByHash($tag, $hash)
    protected function getDatabase($db_config, $tag)
    protected function kickObject($tag)
    protected function reuseObject($tag)
    public function _closeAllDB()
    public function _onException()