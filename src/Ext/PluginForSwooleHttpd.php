<?php
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;

class PluginForSwooleHttpd // impelement SwooleExtAppInterface
{
    use SingletonEx;
    
    public $options=[
    ];
    protected $extDynamicComponentClasses = [];
    public function init($options, $context)
    {
        //$SwooleExt=
        //$SwooleExt::init($options,$this);
        $context->extendComponents(
        [
            'addDynamicComponentClass' => [static::class, 'addDynamicComponentClass'],
        ]);
    }
    public function run()
    {
        //
    }
    // @interface SwooleExtAppInterface
    public function onSwooleHttpdInit($SwooleHttpd, $InCoroutine = false, ?callable $RunHandler)
    {
        $app=$this->appClass::G();
        $app->options['use_super_global'] = true;
        if ($InCoroutine) {
            $app::SG($SwooleHttpd::SG());
            return;
        }
        
        $SwooleHttpd->set_http_exception_handler([$this->appClass,'OnException']);  // TODO
        $SwooleHttpd->set_http_404_handler([$this->appClass, 'On404']);             // 接管 404 处理。
        
        $flag = $SwooleHttpd->is_with_http_handler_root();                         // 如果还有子文件，做404后处理
        $app->options['skip_404_handler'] = $app->options['skip_404_handler'] ?? false;
        $app->options['skip_404_handler'] = $app->options['skip_404_handler'] || $flag;
        
        $funcs = $SwooleHttpd->system_wrapper_get_providers();
        $app->system_wrapper_replace($funcs);
        
        $app->setBeforeRunHandler($RunHandler);
    }
    
    // @interface SwooleExtAppInterface
    public function getStaticComponentClasses()
    {
        $ret = [
            self::class,
            'DuckPhp\Core\App',
            'DuckPhp\Core\AutoLoader',
            'DuckPhp\Core\ExceptionManager',
            'DuckPhp\Core\Configer',
            'DuckPhp\Core\Route',
        ];
        $ret[]=static::class;
        return $ret;
    }
    // @interface SwooleExtAppInterface
    public function getDynamicComponentClasses()
    {
        $ret = [
            'DuckPhp\Core\RuntimeState',
            'DuckPhp\Core\SuperGlobal',
            'DuckPhp\Core\View',
        ];
        return $ret;
    }
    public function addDynamicComponentClass($class)
    {
        $this->extDynamicComponentClasses[] = $class;
    }
    public function deleteDynamicComponentClass($class)
    {
        array_filter($this->extDynamicComponentClasses, function ($v) use ($class) {
            return $v !== $class?true:false;
        });
    }
/*
    protected function cleanClass($input_class)
    {
        $current_class = get_class($input_class::G());
        $input_class::G(new $input_class());
        if ($current_class != $input_class) {
            $this->cleanClass($current_class); // @codeCoverageIgnore
        }
    }
    */
}
