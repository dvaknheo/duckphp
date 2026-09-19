<?php declare(strict_types=1);
/**
 * ZThirdDemo - the main application of the "using a third party app" demo.
 *
 * It mounts ZThirdDemo\Third\System\ThirdApp as a child app under the "shop" prefix,
 * shares components with it, and overrides part of the third party app without
 * touching its files (view / config / resource / controller class).
 */
namespace ZThirdDemo\System;

use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\DuckPhp;
use ZThirdDemo\Override\ShopControllerOverride;
use ZThirdDemo\Third\Controller\MainController as ThirdMainController;
use ZThirdDemo\Third\System\ThirdApp;

class MainApp extends DuckPhp
{
    /** orders collected from the child app through the event bus (demo only) */
    public static $orders = [];

    public $options = [
        'path' => __DIR__ . '/../../',
        'namespace' => 'ZThirdDemo',

        'controller_class_postfix' => 'Controller',
        'controller_method_prefix' => '',
        'controller_url_prefix' => '',

        // serve /res/* from <this app>/res/* without a web server rewrite
        'controller_resource_prefix' => '/res/',

        // the install page is not part of the normal flow; see chapter 31
        'installed' => true,
        'url_install' => 'install',

        'app' => [
            // Mount the third party app. Everything in this array is passed to
            // its init(), so the parent can tune the child without editing it.
            ThirdApp::class => [
                'name' => 'shop',
                'controller_url_prefix' => 'shop/',
                // class level override: the child keeps its route, but the route
                // is executed by our subclass of its controller
                'controller_class_map' => [
                    ThirdMainController::class => ShopControllerOverride::class,
                ],
            ],
        ],

        'ext' => [
            // the event bus is disabled by default, switch it on to use it
            GlobalEvent::class => true,
        ],
    ];

    protected function onInit(): void
    {
        parent::onInit();
        // listen on an event fired from the child app; "" = the root phase
        GlobalEvent::_()->globalOn('third.ordered', '', function ($order_id) {
            self::$orders[] = $order_id;
        });
        // route level override: a legacy URL now points at the mounted app.
        // NOTE: the rewrite key must start with "/" (the hook compares it with
        // "/".$path_info), see chapter 9.
        RouteHookRewrite::_()->assignRewrite('/legacy-shop', 'shop/');
    }
}
