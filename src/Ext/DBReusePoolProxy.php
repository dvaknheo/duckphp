<?php
namespace DNMVCS\Ext;

use DNMVCS\SingletonEx;
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
        $this->appClass=$context?get_calss($context):'';
        
        return $this;
    }
    public function setDBHandler($db_create_handler, $db_close_handler=null)
    {
        $this->db_create_handler=$db_create_handler;
        $this->db_close_handler=$db_close_handler;
    }
    protected function getObject($db_config, $tag)
    {
        if (!isset($this->pools[$tag])) {
            $this->pools[$tag]=[];
        }
        if (empty($this->pools[$tag])) {
            return ($this->db_create_handler)($db_config, $tag);
        }
        $data=array_shift($this->pools[$tag]);
        list($db, $time, $enabled)=$data;
        
        $now=time();
        $is_timeout =($now-$time)>$this->timeout?true:false;
        if ($is_timeout) {
            ($this->db_close_handler)($db, $tag);
            return ($this->db_create_handler)($db_config, $tag);
        }
        return $db;
    }
    protected function reuseObject($db)
    {
        if (!isset($this->pools[$tag])) {
            $this->pools[$tag]=[];
        }
        if (count($this->pools[$tag])>=$this->max_length) {
            ($this->db_close_handler)($db, $tag);
            return;
        }
        $time=time();
        $data=array($db,$time,true);
        
        array_push($this->pools, $data);
    }
    public function onCreate($db_config, $tag)
    {
        return $this->getObject($db_config, $tag);
    }
    protected function checkException()
    {
        if (!$this->appClass) {
            return false;
        }
        // TODO;
        return ($this->appClass)::G()->isInException();
    }
    public function onClose($db, $tag)
    {
        if ($this->checkException()) {
            return;
        }
        return $this->reuseObject($db, $tag);
    }
    public function proxy($dbm)
    {
        if (!$dbm) {
            return;
        }
        list($db_create_handler, $db_close_handler)=$dbm->getDBHandler();
        $this->setDBHandler($db_create_handler, $db_close_handler);
        $dbm->setDBHandler([$this,'onCreate'], [$this,'onClose']);
    }
}
