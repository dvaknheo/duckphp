<?php

namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;
use Redis;

class RedisManager
{
    /*
    [[
                'host'=>'',
                'port'=>'',
                'auth'=>'',
                'select'=>'',
            ]
    */
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'redis_list'=>null,
        'use_context_redis_setting'=>true,
        'enable_simple_cache'=>true,
        'simple_cache_prefix'=>'',
    ];
    const TAG_WRITE=0;
    const TAG_READ=1;
    
    public $options;
    protected $pool=[];
    protected $redis_config_list=[];
    public function init($options = [], $context = null)
    {
        $this->options=array_replace_recursive(static::DEFAULT_OPTIONS, $options);
        $this->redis_config_list=$this->options['redis_list'];
        if ($context) {
            $this->initContext($options, $context);
        }
        if ($this->options['enable_simple_cache']) {
            RedisSimpleCache::G()->init([
                'redis'=>$this->getServer(), 
                'prefix'=>$this->options['simple_cache_prefix']
            ]);
            if (method_exists($context, 'extendComponents')) {
                $context->extendComponents(static::class, ['SimpleCache'], ['S']);
            }
        }
    }
    
    protected function initContext($options=[], $context=null)
    {
        if ($this->options['use_context_redis_setting']) {
            $redis_list=$context::Setting('redis_list')??null;
            if (!isset($redis_list)) {
                $redis_list=$context->options['redis_list']??null;
            }
            if ($redis_list) {
                $this->redis_config_list=$redis_list;
            }
        }
        if (method_exists($context, 'extendComponents')) {
            $context->extendComponents(static::class, ['Redis'], ['S']);
        }
    }
    public static function Redis($tag=0)
    {
        return static::G()->getServer($tag);
    }
    public static function SimpleCache()
    {
        return RedisSimpleCache::G();
    }
    public function getServer($tag=0)
    {
        if (!isset($this->pool[$tag])) {
            $this->pool[$tag]=$this->createServer($this->redis_config_list[$tag]);
        }
        return $this->pool[$tag];
    }
    public function createServer($config)
    {
        $redis = new Redis();
        $redis->connect($config['host'], $config['port']);
        if (isset($config['auth'])) {
            $redis->auth($config['auth']);
        }
        if (isset($config['select'])) {
            $redis->select($config['select']);
        }
        return $redis;
    }
}
