<?php
namespace DNMVCS;
class Toolkit
{
	use DNSingleton;
	// OK Do nothing ,just for autoload
	public static function Init()
	{
	}
	public static function HasInclude($file)
	{
		$a=get_included_files();
		return in_array($a,realpath($file))?true:false;
	}
	public function getTables()
	{
		$ret=[];
		$sql="show tables";
		$data=DN::DB()->fetchAll($sql);
		foreach($data as $v){$ret[]=array_pop(array_values($v));}
		
		return $ret;
	}
	public function DumpDatabaseTableStruct($tables=[])
	{
		$ret=[];
		if(empty($tables)){ $tables=$this->getTables(); }
		foreach($tables as $table){
			try{
				$sql="SHOW CREATE TABLE $table";
				$data=DN::DB()->fetch($sql);
				$str=$data['Create Table'];
				$str=preg_replace('/AUTO_INCREMENT=\d+/','AUTO_INCREMENT=1',$str);
				$ret[$table]=$str;
			}catch(\PDOException $ex){}
		}
		return implode(";\n",$ret);
	}
	public function DumpDatabaseTableData($tables)
	{
		$sql="select * from ";
//		$header="INSERT INTO `$table` () VALUES \n ";
//(,),
	}
    function Export_Database($host,$user,$pass,$name,  $tables=false, $backup_name=false )
    {
        $mysqli = new mysqli($host,$user,$pass,$name); 
        $mysqli->select_db($name); 
        $mysqli->query("SET NAMES 'utf8'");

        $queryTables    = $mysqli->query('SHOW TABLES'); 
        while($row = $queryTables->fetch_row()) 
        { 
            $target_tables[] = $row[0]; 
        }   
        if($tables !== false) 
        { 
            $target_tables = array_intersect( $target_tables, $tables); 
        }
        foreach($target_tables as $table)
        {
            $result         =   $mysqli->query('SELECT * FROM '.$table);  
            $fields_amount  =   $result->field_count;  
            $rows_num=$mysqli->affected_rows;     
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
            $TableMLine     =   $res->fetch_row();
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
            {
                while($row = $result->fetch_row())  
                { //when started (and every after 100 command cycle):
                    if ($st_counter%100 == 0 || $st_counter == 0 )  
                    {
                            $content .= "\nINSERT INTO ".$table." VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)  
                    { 
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ; 
                        }
                        else 
                        {   
                            $content .= '""';
                        }     
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }      
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                    {   
                        $content .= ";";
                    } 
                    else 
                    {
                        $content .= ",";
                    } 
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";
        }
		return $content;

    }
}
trait DNWrapper
{
	protected static $objects=[];
	protected $_object_wrapping;
	protected function _wrap_the_object($object)
	{
		$this->_object_wrapping=$object;
	}
	protected function _call_the_object($method,$args)
	{
		return call_user_func_array([$this->_object_wrapping,$method],$args);
	}

	public static function W($object=null)
	{
		$caller=static::class;
		if($object==null){
			return self::$objects[$caller];
		}
		$self=new $caller();
		$self->_wrap_the_object($object);
		self::$objects[$caller]=$self;
		return $self;
	}
	public function __set($name,$value){
		$this->_object_wrapping->$name=$value;
	}
	public function __get($name){
		return $this->_object_wrapping->$name;
	}
}

//use with DNSingleton
trait DNStaticCall
{
	use DNSingleton;
	//remark ，method do not public
	public static function __callStatic($method, $params)
    {
		$classname=static::class;
        $class=$classname::G();
		return ([$class, $method])(...$params);
    }
}
trait DNSimpleSingleton
{
	protected static $_instances=[];
	public static function G($object=null)
	{
		if($object){
			self::$_instances[static::class]=$object;
			return $object;
		}
		$me=self::$_instances[static::class]??null;
		if(null===$me){
			$me=new static();
			self::$_instances[static::class]=$me;
		}
		return $me;
	}
}

class DNFuncionModifer
{
	protected $FunctionMap=[];
	public static function __callStatic($method, $params)
    {
		$temp=self::$FunctionMap[$method]??null;
		if(null==$temp){
			return ($method)(...$params);
		}
		list($func,$header,$footer)=$temp;
		if(null!==$header){($header)(...$params);}
		if(null!==$func){
			$ret=($func)(...$params);
		}else{
			$ret=($method)(...$params);
		}
		if(null!==$footer){($footer)(...$params);}
		return $ret;
    }
	public static function Assign($functionName,$callback=null,$header=null,$footer=null)
	{
		if(null===$callback && null===$header && null===$footer){
			unset(self::$FunctionMap[$functionName]);
			return;
		}
		self::$FunctionMap[$functionName]=[$callback,$header,$footer];
		
	}
}

class API
{
	protected static function GetTypeFilter()
	{
		return [
			'boolean'=>FILTER_VALIDATE_BOOLEAN  ,
			'bool'=>FILTER_VALIDATE_BOOLEAN  ,
			'int'=>FILTER_VALIDATE_INT,
			'float'=>FILTER_VALIDATE_FLOAT,
			'string'=>FILTER_SANITIZE_STRING,
		];
	}
	public static function Call($class,$method,$input)
	{
		$f=self::GetTypeFilter();
		$reflect = new ReflectionMethod($class,$method);
		
		$params=$reflect->getParameters();
		$args=array();
		foreach ($params as $i => $param) {
			$name=$param->getName();
			if(isset($input[$name])){
				$type=$param->getType();
				if(null!==$type){
					$type=''.$type;
					if(in_array($type,array_keys($f))){
						$flag=filter_var($input[$name],$f[$type],FILTER_NULL_ON_FAILURE);
						DNMVCS::ThrowOn($flag===null,"Type Unmatch: {$name}",-1);
					}
					
				}
				$args[]=$input[$name];
				continue;
			}else if($param->isDefaultValueAvailable()){
				$args[]=$param->getDefaultValue();
			}else{
				DNMVCS::ThrowOn(true,"Need Parameter: {$name}",-2);
			}
			
		}
		
		$ret=$reflect->invokeArgs(new $service(), $args);
		return $ret;
	}
}
class MyArgsAssoc
{
	protected static function GetCalledAssocByTrace($trace)
	{
		list($top,$_)=$trace;
		if($top['object']){
			$reflect=new ReflectionMethod($top['object'],$top['function']);
		}else{
			$reflect=new ReflectionFunction($top['function']);
		}
		$params=$reflect->getParameters();
		$names=array();
		foreach($params as $v){
			$names[]=$v->getName();
		}
		return $names;
	}
	
	public static function GetMyArgsAssoc()
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		return self::GetCalledAssocByTrace($trace);
	}
	
	public static function CallWithMyArgsAssoc($callback)
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		$names=self::GetCalledAssocByTrace($trace);
		return ($callback)($names);
	}
}
class MedooSimpleInstaller
{
	public static function CreateDBInstance($db_config)
	{
		$dsn=$db_config['dsn'];
		list($driver,$dsn)=explode(':',$dsn);
		$dsn=rtrim($dsn,';');
		$a=explode(';',$dsn);
		$dsn_array['driver']=$driver;
		foreach($a as $v){
			list($key,$value)=explode('=',$v);
			$dsn_array[$key]=$value;
		}
		$db_config['dsn']=$dsn_array;
		$db_config['database_type']='mysql';
		
		return new Medoo($db_config);
	}
	public static function CloseDBInstance($db)
	{
		$db->close();
	}
}
final class DidderWrapper
{
	public function __construct($caller,$old_args)
	{
		$this->caller=$caller;
		$this->old_args=$old_args;
	}
	public function __call($name,$args)
	{
		$args=array_merge($this->old_args,$args);
		return call_user_func_array(array($this->caller,$name),$args);
	}
}
trait Didder
{
	public $wrapper;
	public function did($a)
	{
		$this->wrapper=new DidderWrapper($this,func_get_args());
		return $this->wrapper;
	}
}
////
class TestRoot
{
	use  Didder;
	public $str='';
	public function join($a,$b){
		$this->str.="[$a/$b]";
		return $this;
	}
	public function dump()
	{
		var_dump($this->str);
		return $this;
	}
// $root=new TestRoot();
// $t=$root->join("a","b")->dump()->did("c")->join("d")->dump();
}
class Ticker
{
	public static $data=[];
	public static function Start()
	{
		register_tick_function([static::class,'tick_handler']);
	}
	public static function tick_handler()
	{
		$a=debug_backtrace(2,1)[0];
		$file=$a['file'];
		$line=$a['line'];
		echo "$file : $line \n";
		static::$data[$file][$line]=true;
	}
	public static function Stop()
	{
		unregister_tick_function([static::class,'tick_handler']);
		$script_name=realpath($_SERVER['SCRIPT_FILENAME']);
		foreach(static::$data as $filename=>$filedata){
			$ret='';
			$lines=file($filename);
			foreach($lines as $i=>$str){
				$tested=true;
				do{
					$t_str=trim($str);
					$line=$i+1;

					if(isset($filedata[$line])){
						break;
					}
					if(!$t_str || $t_str==='}' || $t_str==='{' |||| $t_str==='}else{' ){
						break;
					}
					
					$p=preg_match('/(if|for|foreach|do|while)\s*\(.*?{$/',$t_str);
					if($p){
						break;
					}
					$p=preg_match('/(class|function|trait|public|protected|private|namespace|declare|use)\s/A',$t_str);
					if($p){
						break;
					}
					
					$tested=false;
				}while(false);
				if($tested){
					//$ret.=rtrim($str,"\n").' // TESTED '.$script_name."\n";
					$ret.=$str;
				}else{
					$ret.=rtrim($str,"\n")." // NT \n";
				}
			}
			$basename=$filename.".log";
			file_put_contents($basename,$ret);
		}
	}
}