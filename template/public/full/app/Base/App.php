<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Base;

use Facades\MY\Base\App as FA;
use JsonRpc\MY\Service\TestSerice;
use DuckPhp\Core\AutoLoader;

class App extends \DuckPhp\App
{
    protected function onInit()
    {
        $this->options['error_404'] = '_sys/error_404';
        $this->options['error_500'] = '_sys/error_500';
        $this->options['error_exception'] = '_sys/error_exception';
        $this->options['error_debug'] = '_sys/error_debug';
        $this->options['ext']['UserSystemDemo\Base\App'] = true;
        
        $this->assignPathNamespace($this->options['path'].'auth/', 'UserSystemDemo');
        $this->options['route_map']['@posts/{post}/comments/{comment:\d+}'] = [$this,'foo'];
        $this->options['route_map_important']['~abc/d(/?|)\w*'] = [$this,'foo'];
        $ret = parent::onInit();
        return $ret;
    }
    protected function onRun()
    {
        return parent::onRun();
    }
    public function foo()
    {
        var_dump(static::getRouteCallingMethod());
        var_dump(static::getParameters());
        var_dump(static::getPathInfo());
    }
}
