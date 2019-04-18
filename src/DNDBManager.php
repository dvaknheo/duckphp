<?php
namespace DNMVCS;

use DNMVCS\DNSingleton;
use DNMVCS\DB\DB;

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
    
    protected $use_strict_db=false;
    public function init($options=[], $context=null)
    {
        $use_db=$options['use_db']??true;
        if (!$use_db) {
            return;
        }
        
        $this->use_strict_db=$options['use_strict_db']??false;
        $this->database_config_list=$options['database_list'];
        $this->db_create_handler=$options['db_create_handler']??[DB::class,'CreateDBInstance'];
        $this->db_close_handler=$options['db_close_handler']??[DB::class,'CloseDBInstance'];
        if ($context) {
            $this->initContext($context);
        }
    }
    protected function initContext($context)
    {
        $db_setting_key=$context->options['db_setting_key']??'database_list';
        $database_list=$context::Setting($db_setting_key)??[];
        
        $this->database_config_list=array_merge($context->options['database_list'], $database_list);
        
        if ($this->use_strict_db) {
            $this->setBeforeGetDBHandler([$context::G(),'checkDBPermission']);
        }
        $context->addBeforeShowHandler([static::class,'CloseAllDB']);
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
    public function CloseAllDB()
    {
        return static::G()->_closeAllDB();
    }
    public function _closeAllDB()
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
