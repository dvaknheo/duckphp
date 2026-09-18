<?php
namespace tests_Ext_PermissionMenu\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;

class NullMetaController implements AdminControllerInterface
{
    /**
     * Returns null on purpose: PermissionMenu must fall back to annotation mode.
     */
    public function __permissionMenuMeta()
    {
        return null;
    }
    /**
     * @menu Null Meta Item
     */
    public function action_index()
    {
    }
}
