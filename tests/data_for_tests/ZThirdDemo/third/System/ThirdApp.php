<?php declare(strict_types=1);
/**
 * ZThirdDemo - the "third party" application.
 *
 * It is a complete little app of its own: its own path, namespace, views,
 * config, resources and controllers. It knows nothing about the app that
 * mounts it.
 */
namespace ZThirdDemo\Third\System;

class ThirdApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../',
        'namespace' => 'ZThirdDemo\Third',

        'controller_class_postfix' => 'Controller',
        'controller_method_prefix' => '',

        // its own resources are served under <mount prefix>/res/*
        // NOTE: no leading slash here - the mount prefix ("shop/") already ends
        // with one, and the prefix is built as "/" . controller_url_prefix . this
        'controller_resource_prefix' => 'res/',

        // an own option, read by its own business/views
        'shop_name' => 'Third Party Shop',
    ];
}
