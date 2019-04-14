<?php
namespace DNMVCS;

class DBReusePoolProxy
{
    use DNSingleton;
    
    public $tag_write='0';
    public $tag_read='1';
    
    protected $db_create_handler;
    protected $db_close_handler;
    protected $db_queue_write;
    protected $db_queue_write_time;
    protected $db_queue_read;
    protected $db_queue_read_time;
    public $max_length=100;
    public $timeout=5;
    public function __construct()
    {
        $this->db_queue_write=new \SplQueue();
        $this->db_queue_write_time=new \SplQueue();
        $this->db_queue_read=new \SplQueue();
        $this->db_queue_read_time=new \SplQueue();
    }
    public function init($max_length=10, $timeout=5, $dbm=null)
    {
        $this->max_length=$max_length;
        $this->timeout=$timeout;
        $this->proxy($dbm);
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
    public function onClose($db, $tag)
    {
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
