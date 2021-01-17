<?php 
namespace tests\DuckPhp\Core;
use DuckPhp\Core\SystemWrapperTrait;
use DuckPhp\SingletonEx\SingletonExTrait;

class SystemWrapperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SystemWrapperTrait::class);
        
        //SystemWrapper::G()->system_wrapper_replace(array $funcs);
        $data=\DuckPhp\Core\App::system_wrapper_get_providers();
        
        SystemWrapperObject::var_dump(DATE(DATE_ATOM));
        SystemWrapperObject::system_wrapper_replace(['var_dump'=>function(...$args){var_dump("!!!!");}]);
        SystemWrapperObject::var_dump(DATE(DATE_ATOM));
        SystemWrapperObject::var_dump2(DATE(DATE_ATOM));
        
        var_dump($data);


        \LibCoverage\LibCoverage::End();
        /*
        SystemWrapper::G()->system_wrapper_replace(array $funcs);
        SystemWrapper::G()->system_wrapper_get_providers();
        SystemWrapper::G()->system_wrapper_call_check($func);
        SystemWrapper::G()->system_wrapper_call($func, $input_args);
        //*/
    }
}
class SystemWrapperObject
{
    
    use SingletonExTrait;
    use SystemWrapperTrait;
    protected $system_handlers=[
        'var_dump'=>null,
        'var_dump2'=>null,
    ];
    public static function var_dump(...$args)
    {
        return static::G()->_var_dump(...$args);
    }
    public function _var_dump(...$args)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        echo "BEGIN";
        var_dump(...$args);
    }
    public static function var_dump2(...$args)
    {
        return static::G()->_var_dump2(...$args);
    }
    public function _var_dump2(...$args)
    {
        $this->system_wrapper_call('var_export', func_get_args());
        try{
        $this->system_wrapper_call('ttt', func_get_args());
        }catch(\ErrorException $ex){
            var_dump($ex);
        }
    }
}
