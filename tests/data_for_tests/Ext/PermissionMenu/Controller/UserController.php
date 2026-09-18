<?php
namespace tests_Ext_PermissionMenu\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;

/**
 * User facing controllers (merges into the Admin\System directory).
 *
 * @menu_directory Admin\System
 * @menu_weight 1
 */
class UserController implements AdminControllerInterface
{
    /**
     * @menu Profile
     * @menu_weight 3
     */
    public function action_profile()
    {
    }
    /**
     * Not annotated, becomes an action named after the method.
     */
    public function action_orders()
    {
    }
    /**
     * @menu_action Orders List
     * @menu_directory Users
     */
    public function action_order_list()
    {
    }
}
