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
    public $tag_write='0';
    public $tag_read='1';
    public $max_length=100;
    public $timeout=5;
    
    protected $db_create_handler;
    protected $db_close_handler;
    protected $db_queue_write;
    protected $db_queue_write_time;
    protected $db_queue_read;
    protected $db_queue_read_time;
    
    protected $appClass;
    
    protected $dn;
    
    public function __construct()
    {
        $this->db_queue_write=new SplQueue();
        $this->db_queue_write_time=new SplQueue();
        $this->db_queue_read=new SplQueue();
        $this->db_queue_read_time=new SplQueue();
    }
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
    protected function getObject($queue, $queue_time, $db_config, $tag)
    {
        if ($queue->isEmpty()) {
            return ($this->db_create_handler)($db_config, $tag);
        }
        $db=$queue->shift();
        $time=$queue_time->shift();
        $now=time();
        $is_timeout =($now-$time)>$this->timeout?true:false;
        if ($is_timeout) {
            ($this->db_close_handler)($db, $tag);
            return ($this->db_create_handler)($db_config, $tag);
        }
        return $db;
    }
    protected function reuseObject($queue, $queue_time, $db)
    {
        if (count($queue)>=$this->max_length) {
            ($this->db_close_handler)($db, $tag);
            return;
        }
        $time=time();
        $queue->push($db);
        $queue_time->push($time);
    }
    public function onCreate($db_config, $tag)
    {
        if ($tag!=$this->tag_write) {
            return $this->getObject($this->db_queue_write, $this->db_queue_write_time, $db_config, $tag);
        } else {
            return $this->getObject($this->db_queue_read, $this->db_queue_read_time, $db_config, $tag);
        }
    }
    protected function checkException()
    {
        if (!$this->appClass) {
            return false;
        }
        return ($this->appClass)::G()->isInException();
    }
    public function onClose($db, $tag)
    {
        if ($this->checkException()) {
            return;
        }
        if ($tag!=$this->tag_write) {
            return $this->reuseObject($this->db_queue_write, $this->db_queue_write_time, $db);
        } else {
            return $this->reuseObject($this->db_queue_read, $this->db_queue_read_time, $db);
        }
    }
    public function proxy($dbm)
    {
        if (!$dbm) {
            return;
        }
        list($db_create_handler, $db_close_handler)=$dnm->getDBHandler();
        $this->setDBHandler($db_create_handler, $db_close_handler);
        $dnm->setDBHandler([$this,'onCreate'], [$this,'onClose']);
    }
}
