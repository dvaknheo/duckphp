<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class PluginForSwooleHttpd extends ComponentBase // , SwooleExtAppInterface
{
    public $options = [
        'swoole_ext_class' => 'SwooleHttpd\\SwooleExt',
    ];
    protected $context_class;
    protected $SwooleHttpd;
    
    public function init(array $options, object $context = null)
    {
        if (PHP_SAPI !== 'cli') {
            return $this; // @codeCoverageIgnore
        }
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        $this->context_class = $context ? get_class($context) : null;
        $SwooleExt = $this->options['swoole_ext_class'];
        $SwooleExt::G()->init($options, $this);
        
        $this->is_inited = true; //override
        return $this;
    }
    // @interface SwooleExtAppInterface
    public function run()
    {
        return $this->context_class::G()->run();
    }
    // @interface SwooleExtAppInterface
    public function onSwooleHttpdInit($SwooleHttpd = null, ?callable $RunHandler = null)
    {
        $this->SwooleHttpd = $SwooleHttpd;
        
        $app = $this->context_class::G();
        $app->options['use_super_global'] = true;
        $app->options['skip_exception_check'] = true;
        
        $SwooleHttpd->set_http_exception_handler([$this->context_class,'onException']);  //TODO
        $SwooleHttpd->set_http_404_handler([$this->context_class, 'On404']);             // 接管 404 处理。
        
        $flag = $SwooleHttpd->is_with_http_handler_root();                         // 如果还有子文件，做404后处理
        $app->options['skip_404_handler'] = $app->options['skip_404_handler'] ?? false;
        $app->options['skip_404_handler'] = $app->options['skip_404_handler'] || $flag;
        
        $app::system_wrapper_replace($SwooleHttpd::system_wrapper_get_providers());  //  替换系统函数
        
        $app->replaceDefaultRunHandler($RunHandler);
    }
    // @interface SwooleExtAppInterface
    public function onSwooleHttpdStart($SwooleHttpd = null)
    {
        $this->context_class::G()->replaceDefaultRunHandler(null);
        //$this->context_class::G()->addRouteHook([static::class,'Hook'], 'prepend-outter');
    }
    // @interface SwooleExtAppInterface
    public function onSwooleHttpdRequest($SwooleHttpd)
    {
        //$this->context_class::G()::SG($SwooleHttpd::SG());
        return;
    }
    // @interface SwooleExtAppInterface
    public function getStaticComponentClasses()
    {
        return $this->context_class::G()->getStaticComponentClasses();
    }
    // @interface SwooleExtAppInterface
    public function getDynamicComponentClasses()
    {
        return $this->context_class::G()->getDynamicComponentClasses();
    }
}
