<?php
namespace tests_Ext_PermissionMenu\System;

class ChildMenuApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../',
        'namespace' => 'tests_Ext_PermissionMenu',
        'permission_menu_tree_for_admin' => 'menu_child.json',
        'app' => [
            LeafMenuApp::class => ['name' => 'leaf'],
        ],
    ];
}
