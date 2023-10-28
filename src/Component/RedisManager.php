<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;
use Redis;

class RedisManager extends ComponentBase
{
    /*
    redis_list=>
    [[
                'host'=>'',
                'port'=>'',
                'auth'=>'',
                'select'=>'',
            ]
    ]
    */
    public $options = [
        'redis' => null,
        'redis_list' => null,
        'redis_list_reload_by_setting' => true,
        'redis_list_try_single' => true,
    ];
    const TAG_WRITE = 0;
    const TAG_READ = 1;
    protected $pool = [];
    protected $redis_config_list = [];
    public function init(array $options, ?object $context = null)
    {
        if ($this->is_inited || $options['redis_force_reinit'] ?? false) {
            return $this;
        }
        return parent::init($options, $context);
    }
    //@override
    protected function initOptions(array $options)
    {
        //TODO $this->is_inited,
        $redis_list = $this->options['redis_list'];
        if (!isset($redis_list) && $this->options['redis_list_try_single']) {
            $redis = $this->options['redis'];
            $redis_list = $redis ? array($redis) : null;
        }
        $this->redis_config_list = $redis_list;
    }
    //@override
    protected function initContext(object $context)
    {
        //$this->context_class = $context
        //($this->context_class)::G()->_Setting();
        if ($this->options['redis_list_reload_by_setting']) {
            /** @var mixed */
            $setting = $context->_Setting(); /** @phpstan-ignore-line */
            $redis_list = $setting['redis_list'] ?? null;
            if (!isset($redis_list) && $this->options['redis_list_try_single']) {
                $redis = $setting['redis'] ?? null;
                $redis_list = $redis ? array($redis) : null;
            }
            $this->redis_config_list = $redis_list ?? $this->redis_config_list;
        }
    }
    public static function Redis($tag = 0)
    {
        return static::G()->getServer($tag);
    }
    public function getServer($tag = 0)
    {
        if (!isset($this->pool[$tag])) {
            $this->pool[$tag] = $this->createServer($this->redis_config_list[$tag]);
        }
        return $this->pool[$tag];
    }
    public function createServer($config)
    {
        $redis = new Redis();
        $redis->connect($config['host'], (int)$config['port']);
        if (isset($config['auth'])) {
            $redis->auth($config['auth']);
        }
        if (isset($config['select'])) {
            $redis->select((int)$config['select']);
        }
        return $redis;
    }
}
