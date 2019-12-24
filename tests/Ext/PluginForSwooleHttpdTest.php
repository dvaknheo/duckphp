<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\PluginForSwooleHttpd;
use DuckPhp\App;
use DuckPhp\Core\SingletonEx;

class PluginForSwooleHttpdTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(PluginForSwooleHttpd::class);
        $options=[
            'swoole_ext_class' => FakeSwooleExt::class,
        ];

        $context=App::G();
        PluginForSwooleHttpd::G()->init($options, $context);
        PluginForSwooleHttpd::G()->getStaticComponentClasses();
        PluginForSwooleHttpd::G()->getDynamicComponentClasses();
        PluginForSwooleHttpd::G()->run();
        
        $SwooleHttpd=new FakeSwooleHttpd();
        
PluginForSwooleHttpd::G()->onSwooleHttpdInit($SwooleHttpd,null);
PluginForSwooleHttpd::G()->onSwooleHttpdStart($SwooleHttpd);
PluginForSwooleHttpd::G()->onSwooleHttpdRequest($SwooleHttpd);

        
        \MyCodeCoverage::G()->end(PluginForSwooleHttpd::class);
        $this->assertTrue(true);
    }
}
class FakeSwooleExt
{
    use SingletonEx;
    public function init($options, $context)
    {
    }
}
class FakeSwooleHttpd
{
    public static function SG()
    {
        return null;
    }
    public static function system_wrapper_get_providers()
    {
        return [];
    }
    public function is_with_http_handler_root()
    {
        return true; // return false;
    }
    public function set_http_exception_handler(callable $callback)
    {
        return;
    }
    public function set_http_404_handler(callable $callback)
    {
        return;
    }
}