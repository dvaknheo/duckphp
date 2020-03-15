# Ext\DBManager

## 简介

## 选项
'db_create_handler' => null,
'db_close_handler' => null,
'db_exception_handler' => null,
'before_get_db_handler' => null,

'database_list' => null,
'use_context_db_setting' => true,
'db_close_at_output' => true,
## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    protected function initContext($options = [], $context = null)
    public static function CloseAllDB()
    public function OnException()
    public static function DB($tag = null)
    public static function DB_W()
    public static function DB_R()
    public function setDBHandler($db_create_handler, $db_close_handler = null, $db_exception_handler = null)
    public function setBeforeGetDBHandler($before_get_db_handler)
    public function getDBHandler()
    public function _DB($tag = null)
    protected function getDatabase($db_config, $tag)
    public function _DB_W()
    public function _DB_R()
    public function _closeAllDB()
    public function _onException()