<?php
namespace tests_Ext_PermissionMenu\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;

/**
 * Admin panel controllers.
 *
 * @menu_directory Admin\System
 * @menu_icon fa fa-folder
 * @menu_weight 10
 */
class AdminController implements AdminControllerInterface
{
    /**
     * @menu Dashboard Panel
     * @menu_icon home
     * @menu_weight 5
     */
    public function action_index()
    {
    }
    /**
     * @menu_action User List
     * @menu_directory Users\Manage
     * @menu_directory_url users/manage
     * @menu_weight -1
     */
    public function action_list()
    {
    }
    /**
     * @menu_permission #edit Edit User
     * @menu_permission /abs/place Abs Place Here
     * @menu_permission #noname
     */
    public function action_edit()
    {
    }
    public function action_plain()
    {
    }
    /**
     * @menu_icon
     */
    public function action_bare_icon()
    {
    }
}
