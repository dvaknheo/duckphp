<?php
namespace MY\Base;
use Facades\MY\Base\App as FA;
use JsonRpc\MY\Service\TestSerice;
use DuckPhp\Core\AutoLoader;
class App extends \DuckPhp\App
{
	protected function onInit()
	{
        $this->options['is_debug']=true;
        $this->is_debug=true;
        
        $this->assignPathNamespace($this->options['path'].'second/', 'Second');
        $this->assignPathNamespace($this->options['path'].'myplugin/', 'MyPlugin');
        
        
        $this->options['error_500']='_sys/error-500';
        $this->options['error_exception']='_sys/error-exception';
        $this->options['error_debug']='_sys/error-debug';
        try{
            $ret=parent::onInit();
        }catch(\Throwable $ex){
            var_dump($ex);
        }
        return $ret;
	}
    protected function onRun()
    {
       return parent::onRun();
    }
}