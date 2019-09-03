<?php
namespace DNMVCS\Ext;

use DNMVCS\SingletonEx;
use DNMVCS\DB\DB;

class DBManager
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'db_create_handler'=>null,
        'db_close_handler'=>null,
        'db_excption_handler'=>null,
        'before_get_db_handler'=>null,
        
        'database_list'=>null,
        'use_context_db_setting'=>true,
    ];
    public $tag_write=0;
    public $tag_read='1';
    
    protected $database_config_list=[];
    protected $databases=[];
    
    protected $db_create_handler=null;
    protected $db_close_handler=null;
    protected $db_excption_handler=null;
    
    protected $before_get_db_handler=null;
    protected $use_context_db_setting=true;
    
    public function init($options=[], $context=null)
    {
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, $options);
        
        $this->before_get_db_handler=$options['before_get_db_handler']??null;
        $this->database_config_list=$options['database_list'];
        $this->db_create_handler=$options['db_create_handler']??[DB::class,'CreateDBInstance'];
        $this->db_close_handler=$options['db_close_handler']??[DB::class,'CloseDBInstance'];
        $this->db_excption_handler=$options['db_excption_handler']??null;
        $this->use_context_db_setting=$options['use_context_db_setting'];
        if ($context) {
            $this->initContext($options, $context);
        }
        return $this;
    }
    protected function initContext($options=[], $context=null)
    {
        if ($this->use_context_db_setting) {
            $database_list=$context::Setting('database_list')??null;
            if (!isset($database_list)) {
                $database_list=$context->options['database_list']??null;
            }
            $this->database_config_list=$database_list;
        }
        
        // before_get_db_handler
        if (is_array($this->before_get_db_handler) && $this->before_get_db_handler[0]===null) {
            $this->before_get_db_handler[0]=get_class($context);
        }
        $context->addBeforeShowHandler([static::class,'CloseAllDB']);
    }

    public function setDBHandler($db_create_handler, $db_close_handler=null, $db_excption_handler=null)
    {
        $this->db_create_handler=$db_create_handler;
        $this->db_close_handler=$db_close_handler;
        $this->db_excption_handler=$db_excption_handler;
    }
    public function setBeforeGetDBHandler($before_get_db_handler)
    {
        $this->before_get_db_handler=$before_get_db_handler;
    }
    public function getDBHandler()
    {
        return [$this->db_create_handler,$this->db_close_handler];
    }
    public function _DB($tag=null)
    {
        if (isset($this->before_get_db_handler)) {
            ($this->before_get_db_handler)($this, $tag);
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
        foreach ($this->databases as $tag=>$v) {
            ($this->db_close_handler)($v,$tag);
        }
        $this->databases=[];
    }
    public function OnException()
    {
        return static::G()->_onException();
    }
    public function _onException()
    {
        if (!$this->db_excption_handler) {
            return;
        }
        foreach ($this->databases as $tag=>$v) {
            ($this->db_excption_handler)($v,$tag);
        }
        $this->databases=[];
    }
}
