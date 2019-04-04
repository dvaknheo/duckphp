<?php
namespace DNMVCS;

class DNDBManager
{
    use DNSingleton;
    
    public $tag_write=0;
    public $tag_read='1';
    
    protected $database_config_list=[];
    protected $databases=[];
    
    protected $db_create_handler=null;
    protected $db_close_handler=null;
    
    protected $before_get_db_handler=null;
    public function init($database_config_list=[])
    {
        $this->database_config_list=$database_config_list;
    }
    public function setDBHandler($db_create_handler, $db_close_handler=null)
    {
        $this->db_create_handler=$db_create_handler;
        $this->db_close_handler=$db_close_handler;
    }
    public function setBeforeGetDBHandler($before_get_db_handler)
    {
        $this->before_get_db_handler=$before_get_db_handler;
    }
    public function getDBHandler()
    {
        return [$db_create_handler,$db_close_handler];
    }
    public function _DB($tag=null)
    {
        if (isset($this->before_get_db_handler)) {
            ($this->before_get_db_handler)($tag);
        }
        if (!isset($tag)) {
            $t=array_keys($this->database_config_list);
            $tag=$t[0];
        }
        
        if (!isset($this->databases[$tag])) {
            $db_config=$this->database_config_list[$tag]??null;
            if ($db_config===null) {
                return null;
            }
            $this->databases[$tag]=($this->db_create_handler)($db_config, $tag);
        }
        return $this->databases[$tag];
    }
    public function _DB_W()
    {
        return $this->_DB($this->tag_write);
    }
    public function _DB_R()
    {
        if (!isset($this->database_config_list[$this->tag_read])) {
            return $this->_DB();
        }
        return $this->_DB($this->tag_read);
    }
    public function closeAllDB()
    {
        if (!$this->db_close_handler) {
            return;
        }
        foreach ($this->databases as $v) {
            ($this->db_close_handler)($v);
        }
        $this->databases=[];
    }
}
