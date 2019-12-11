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
        
        $ret = parent::onInit();
        return $ret;
    }
    protected function onRun()
    {
        return parent::onRun();
    }
}
