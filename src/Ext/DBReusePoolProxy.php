<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Ext\DBManager;

class DBReusePoolProxy extends DBManager // @codeCoverageIgnoreStart
{
    public $options = [
        'db_reuse_size' => 100,
        'db_reuse_timeout' => 5,
    ];
    protected $db_reuse_size;
    protected $db_reuse_timeout;
    protected $pools = [];
    protected $indexs = [];
    protected $is_static = true;
    public function __construct()
    {
        $this->options = array_replace_recursive($this->options, (new parent())->options); //merge parent's options;
        parent::__construct();
    }
    protected function initOptions(array $options)
    {
        parent::initOptions($options);
        
        $this->db_reuse_size = $this->options['db_reuse_size'] ?? 100;
        $this->db_reuse_timeout = $this->options['db_reuse_timeout'] ?? 5;
    }
    //@override
    protected function initContext(object $context)
    {
        parent::initContext($context);
        if (method_exists($context, 'addDynamicComponentClass')) {
            $context->addDynamicComponentClass(static::class);
        }
    }
    //////////////////
    protected function getObjectIndex($tag)
    {
        $cid = 0;
        if (!class_exists('Swoole\Coroutine')) {
            return;
        }
        try {
            $func = ['Swoole\Coroutine','getuid'];
            if (is_callable($func)) {
                $cid = ($func)();
            }
            $cid = ($cid <= 0)?0:$cid;
        } catch (\Throwable $ex) {
        }
        return ''.$cid.':'.$tag;
    }

    protected function getObjectByHash($tag, $hash)
    {
        $wrapper = $this->pools[$tag][$hash] ?? null;
        
        if (!$wrapper) {
            return null;
        }
        $now = time();
        if (($now - $wrapper['Time']) >= $this->db_reuse_timeout) {
            return null;
        }
        $this->pools[$tag][$hash]['Time'] = $now;
        
        return $this->pools[$tag][$hash]['Object'];
    }
    protected function getDatabase($db_config, $tag)
    {
        $object = null;
        $this->pools[$tag] = $this->pools[$tag] ?? [];
        
        $index = $this->getObjectIndex($tag);
        $hash = $this->indexs[$index] ?? null;
        if ($hash !== null) {
            $object = $this->getObjectByHash($tag, $hash);
            if ($object !== null) {
                return $object;
            }
        }
        
        foreach ($this->pools[$tag] as $hash => $v) {
            $object = $this->getObjectByHash($tag, $hash);
            if ($object !== null) {
                return $object;
            }
        }
        
        //full to kick out
        if (count($this->pools[$tag]) >= $this->db_reuse_size) {
            $v = array_shift($this->pools[$tag]);
            unset($this->indexs[$v['Index']]);
            if ($this->db_close_handler) {
                ($this->db_close_handler)($v['Object'], $tag);
            }
        }
        
        $object = ($this->db_create_handler)($db_config, $tag);
        
        $key = spl_object_hash($object);
        $this->indexs[$index] = $key;
        $this->pools[$tag][$key] = [
            'Object' => $object,
            'Userable' => false,
            'Time' => time(),
            'Index' => $index,
        ];
        return $object;
    }
    protected function kickObject($tag)
    {
        $index = $this->getObjectIndex($tag);
        $key = $this->indexs[$index] ?? null;
        if ($key === null) {
            return;
        }

        $object = $this->pools[$tag][$key]['Object'] ?? null;
        if ($object === null) {
            return;
        }
        
        $index = $this->getObjectIndex($tag);
        unset($this->indexs[$index]);
        if ($this->db_exception_handler) {
            ($this->db_exception_handler)($object, $tag);
        } elseif ($this->db_close_handler) {
            ($this->db_close_handler)($object, $tag);
        }
    }
    protected function reuseObject($tag)
    {
        $index = $this->getObjectIndex($tag);
        $key = $this->indexs[$index] ?? null;
        if ($key === null) {
            return;
        }
        
        if (!isset($this->pools[$tag][$key])) {
            return;
        }
        $this->pools[$tag][$key]['Time'] = time();
        $this->pools[$tag][$key]['Index'] = null;
        $this->pools[$tag][$key]['Useable'] = true;
    }
    public function _closeAllDB()
    {
        foreach ($this->database_config_list as $tag => $v) {
            $this->reuseObject($tag);
        }
    }
    public function _onException()
    {
        foreach ($this->database_config_list as $tag => $v) {
            $this->kickObject($tag);
        }
    }
}   // @codeCoverageIgnoreEnd
