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

    protected $defaultResult = false;
    /**
     * Whether runSelfMiddleware() ran in the current doHook() call.
     * Still false when a middleware short-circuits (never calls $next).
     * @var bool
     */
    protected $ranInner = false;

    public function __construct()
    {
        $this->request = new \stdClass();
        $this->response = new \stdClass();
    }

    //@override
    protected function initContext(object $context): void
    {
        //Route::_()->addRouteHook([static::class,'Hook'], 'prepend-inner');
        RouteHookManager::_()->attachPreRun()->append([static::class,'Hook']);
    }
    public static function Hook($path_info)
    {
        return static::_()->doHook($path_info);
    }
    public function doHook($path_info = '')
    {
        $middleware = array_reverse($this->options['middleware']);

        $this->defaultResult = false;
        $this->ranInner = false;

        $callback = array_reduce($middleware, function ($carry, $pipe) {
            return function () use ($carry, $pipe) {
                if (is_string($pipe) && !\is_callable($pipe)) {
                    if (false !== strpos($pipe, '@')) {
                        list($class, $method) = explode('@', $pipe);
                        /** @var callable */ $pipe = [$class::_(), $method];
                    } elseif (false !== strpos($pipe, '->')) {
                        list($class, $method) = explode('->', $pipe);
                        /** @var callable */ $pipe = [ new $class(), $method];
                    }
                }

                $response = $pipe($this->getRequest(), $carry);
                return $response;
            };
        }, function () {
            // Set here (not inside runSelfMiddleware) so overriding runSelfMiddleware()
            // -- a documented extension point -- cannot lose the flag.
            $this->ranInner = true;
            return $this->runSelfMiddleware();
        });
        $response = $callback();
        $this->onPostMiddleware();

        if (!$this->ranInner && $this->isHandledResponse($response)) {
            // A middleware short-circuited (it never called $next), so the default
            // route callback did not run: emit its response and tell Route::run()
            // that this request is handled -- otherwise Route runs the controller anyway.
            $this->outputResponse($response);
            return true;
        }
        return $this->defaultResult;
    }
    /**
     * Is this response a short-circuit result? null/false means "not handled",
     * so the route keeps going the old way (the default callback still runs).
     * @param mixed $response
     */
    protected function isHandledResponse($response): bool
    {
        return $response !== null && $response !== false;
    }
    /**
     * Send the short-circuit response out. Subclasses may override this
     * (e.g. to feed a response object of their own).
     * @param mixed $response
     */
    protected function outputResponse($response): void
    {
        if (is_string($response) && $response !== '') {
            echo $response;
        }
    }
    protected function runSelfMiddleware(): string
    {
        $this->defaultResult = Route::_()->defaultRunRouteCallback();
        return $this->getResponse();
    }
    protected function onPostMiddleware(): void
    {
    }
    protected function getResponse(): string
    {
        return '';
    }
    protected function getRequest()
    {
        return $this->request;
    }
}
