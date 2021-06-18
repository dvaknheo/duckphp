# DuckPhp\Ext\RedisManager
[toc]

## 简介
Redis 管理类
## 选项
        'redis' => null,
redis 设置

        'redis_list' => null,
redis 列表

        'redis_list_reload_by_setting' => true,
是否从设置里再入 redis 设置

        'redis_auto_extend_method' => true,
自动增加Reis扩展方法到助手方法

        'redis_list_try_single' => true,
redis 设置是否同时支持单个和多个
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
