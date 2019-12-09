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
        
        $this->options['error_404']='_sys/error_404';
        $this->options['error_500']='_sys/error_500';
        $this->options['error_exception']='_sys/error_exception';
        $this->options['error_debug']='_sys/error_debug';
        
        $this->assignPathNamespace($this->options['path'].'auth/', 'Project');
        $this->options['ext']['Project\Base\App']=true;
        
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