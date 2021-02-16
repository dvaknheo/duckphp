<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;
use DuckPhp\Ext\RouteHookManager;

class MyMiddlewareManager extends ComponentBase
{
    public $options = [
        'middleware' => [],
        //'middleware_auto_extend_method' => false,
    ];
    public $request;
    public $response;
    
    protected $context_class;
    protected $defaultResult = false;
    
    public function __construct()
    {
        $this->request = new \stdClass();
        $this->response = new \stdClass();
    }

    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        //($this->context_class)::Route()->addRouteHook([static::class,'Hook'], 'prepend-inner');
        RouteHookManager::G()->attachPreRun()->append([static::class,'Hook']);
    }
    public static function Hook($path_info)
    {
        return static::G()->doHook($path_info);
    }
    public function doHook($path_info = '')
    {
        $middleware = array_reverse($this->options['middleware']);

        $callback = array_reduce($middleware, function ($carry, $pipe) {
            return function () use ($carry, $pipe) {
                if (is_string($pipe) && !\is_callable($pipe)) {
                    if (false !== strpos($pipe, '@')) {
                        list($class, $method) = explode('@', $pipe);
                        /** @var callable */ $pipe = [$class::G(), $method];
                    } elseif (false !== strpos($pipe, '->')) {
                        list($class, $method) = explode('->', $pipe);
                        /** @var callable */ $pipe = [ new $class(), $method];
                    }
                }
            
                $response = $pipe($this->getRequest(), $carry);
                return $response;
            };
        }, function () {
            return $this->runSelfMiddleware();
        });
        $callback();
        $this->onPostMiddleware();
        return $this->defaultResult;
    }
    protected function runSelfMiddleware()
    {
        $this->defaultResult = Route::G()->defaultRunRouteCallback();
        return $this->getResponse();
    }
    protected function onPostMiddleware()
    {
    }
    protected function getResponse()
    {
        return '';
    }
    protected function getRequest()
    {
        return $this->request;
    }
}
