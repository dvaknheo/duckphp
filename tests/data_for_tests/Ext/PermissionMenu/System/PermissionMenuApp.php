<?php
namespace tests_Ext_PermissionMenu\System;

use DuckPhp\Ext\PermissionMenu;

class PermissionMenuApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../',
        'namespace' => 'tests_Ext_PermissionMenu',

        'controller_class_postfix' => 'Controller',
        'controller_method_prefix' => 'action_',
        'controller_url_prefix' => '',

        // read by PermissionMenu::getMenuJsonFileConfig()
        'permission_menu_tree_for_admin' => null,

        'app' => [
            GrandMenuApp::class => ['name' => 'grand'],
            ChildMenuApp::class => ['name' => 'child'],
        ],
        'ext' => [
            PermissionMenu::class => true,
        ],
    ];
    public function foo()
    {
    }
}
