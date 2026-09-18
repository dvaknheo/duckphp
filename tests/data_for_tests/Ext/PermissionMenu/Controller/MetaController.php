<?php
namespace tests_Ext_PermissionMenu\Controller;

use DuckPhp\Ext\PermissionMenuMetaInterface;
use DuckPhp\GlobalAdmin\AdminControllerInterface;

class MetaController implements AdminControllerInterface, PermissionMenuMetaInterface
{
    public function __permissionMenuMeta(): array
    {
        return [
            ['name' => 'Meta Menu', 'type' => 1, 'url' => 'Meta/index', 'weight' => 3],
            ['name' => 'Meta Action'],
        ];
    }
    public function action_index()
    {
    }
}
