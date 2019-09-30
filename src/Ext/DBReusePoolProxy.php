<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Ext\DBManager;
use SplQueue;

class DBReusePoolProxy
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'db_reuse_size' => 100,
        'db_reuse_timeout' => 5,
        'dbm' => null,
    ];
    public $max_length=100;
    public $timeout=5;
    
    protected $db_create_handler;
    protected $db_close_handler;
    protected $db_exception_handler;
    
    protected $pools=[];
    protected $appClass;
    
    public function init($options=[], $context=null)
    {
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, $options);
        
        $this->max_length=$options['db_reuse_size'];
        $this->timeout=$options['db_reuse_timeout'];
        
        $dbm=$options['dbm'];
        $dbm=$dbm??DBManager::G();
        $dbm=is_string($dbm)?$dbm::G():$dbm;
        
        $this->proxy($dbm);
        $this->appClass=$context? \get_class($context):null;
        
        return $this;
    }
    public function proxy($dbm)
    {
        if (!$dbm) {
            return;
        }
        list($db_create_handler, $db_close_handler, $db_exception_handler)=$dbm->getDBHandler();
        $this->setDBHandler($db_create_handler, $db_close_handler, $db_exception_handler);
        $dbm->setDBHandler([$this,'onCreate'], [$this,'onClose'], [$this,'onException']);  //将会被 clean
    }
    public function setDBHandler($db_create_handler, $db_close_handler=null, $db_exception_handler=null)
    {
        $this->db_create_handler=$db_create_handler;
        $this->db_close_handler=$db_close_handler;
        $this->db_exception_handler=$db_exception_handler;
    }
    public function onCreate($db_config, $tag)
    {
        return $this->getObject($db_config, $tag);
    }
    public function onClose($db, $tag)
    {
        return $this->reuseObject($db, $tag);
    }
    public function onException($db, $tag)
    {
        return $this->kickObject($db, $tag);
    }
    protected function getObject($db, $tag)
    {
        $this->pools[$tag]=$this->pools[$tag]??[];
        $pool=&$this->pools[$tag];
        
        foreach ($pool as &$v) {
            if (!$v['Userable']) {
                continue;
            }
            $now=time();
            if (($now-$v['Userable'])>$this->timeout) {
                continue;
            }
            $v['Userable']=true;
            return $v['object'];
        }
        if (count($pool)>=$this->max_length) {
            array_shift($pool);
        }
        $object=($this->db_create_handler)($db, $tag);
        
        $key=spl_object_hash($object);
        $pool[$key]=[
            'Object'=>$object,
            'Userable'=>true,
            'Time'=>time(),
        ];
        return $object;
    }
    protected function kickObject($db, $tag)
    {
        $this->pools[$tag]=$this->pools[$tag]??[];
        $pool=&$this->pools[$tag];
        $key=spl_object_hash($db);
        
        unset($pool[$key]);
    }
    protected function reuseObject($db, $tag)
    {
        $this->pools[$tag]=$this->pools[$tag]??[];
        $pool=&$this->pools[$tag];
        
        $key=spl_object_hash($db);
        if (!isset($pool[$key])) {
            return;
        }
        $a=&$pool[$key];
        $a['Time']=time();
        $a['Useable']=true;
    }
}
