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
    protected $options_project=[
        'error_404'=>'_sys/error_404',
        'error_exception'=>'error_exception',
        'error_debug'=>'error_debug',
        'ext'=>[
            'UserSystemDemo\\Base\\App'=>true,
        ],
    ];

    public function onPrepare()
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
