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
        
        $this->options['ext']['DNMVCS\Ext\FacadesAutoLoader']=[
            'facades_namespace'=>'Facades',
        ];
        $this->options['ext']['DNMVCS\Ext\JsonRpcExt']=[
            'jsonrpc_backend'=>['http://test.dnmvcs.dev/json_rpc','127.0.0.1:80'],
        ];
        /*
        $this->options['ext']['Second\Base\App']=[
            'plugin_mode'=>true,
            'plugin_path_namespace'=>'second',
            'plugin_namespace'=>'Second',
        ];
        */
        
        //$this->options['ext']['MyPlugin\Base\App']=true;
        
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