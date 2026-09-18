<?php
namespace tests_Ext_PermissionMenu\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;

class BadMetaController implements AdminControllerInterface
{
    public function __permissionMenuMeta()
    {
        throw new \Exception('meta failed on purpose');
    }
    /**
     * @menu Bad Meta Item
     */
    public function action_index()
    {
    }
}
