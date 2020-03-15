# Ext\RedisManager

## 简介
Redis 管理类
## 选项
        'redis_list' => null,
        'use_context_redis_setting' => true,
        'enable_simple_cache' => true,
        'simple_cache_prefix' => '',
/*
    [
                'host'=>'',
                'port'=>'',
                'auth'=>'',
                'select'=>'',
            ]
    */
## 方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    protected function initContext($options = [], $context = null)
    public static function Redis($tag = 0)
    public static function SimpleCache()
    public function getServer($tag = 0)
    public function createServer($config)