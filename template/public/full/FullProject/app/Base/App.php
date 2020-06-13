<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Base;

use Facades\MY\Base\App as FA;
use JsonRpc\MY\Service\TestSerice;
use DuckPhp\Core\AutoLoader;

class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'error_404'=>'_sys/error_404',
        'error_500'=>'_sys/error_500',
        'error_debug'=>'_sys/error_debug',
        'ext'=>[
            'UserSystemDemo\\Base\\App'=>true,
        ],
    ];

    protected function onPrepare()
    {
        $this->options['route_map']['@posts/{post}/comments/{comment:\d+}'] = [$this,'foo'];
        $this->options['route_map_important']['^abc/d(/?|)\w*'] = [$this,'foo'];
        
        $this->assignPathNamespace($this->options['path'].'auth/', 'UserSystemDemo');
    }
    protected function onInit()
    {
    }
    public function foo()
    {
        var_dump("hit!");
    }
}
