# DuckPhp\Ext\RedisManager
[toc]

## 简介
Redis 管理类
## 选项
        'redis' => null,
        'redis_list' => null,
        'redis_list_reload_by_setting' => true,
        'redis_auto_extend_method' => true,
    
        'redis_list_try_single' => true,
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
    protected function initContext(object $context)
    protected function initOptions(array $options)
    public static function Redis($tag = 0)
    public function getServer($tag = 0)
    public function createServer($config)


​    
### RedisManager

redis 管理器。 redis 入口
