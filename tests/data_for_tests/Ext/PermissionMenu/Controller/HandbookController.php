<?php
namespace tests_Ext_PermissionMenu\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;

/**
 * Documentation controller: the directory url comes from @menu_directory_url.
 *
 * @menu_directory Handbook
 * @menu_directory_url handbook/index
 * @menu_icon book
 * @menu_weight 20
 */
class HandbookController implements AdminControllerInterface
{
    /**
     * @menu Handbook Home
     */
    public function action_index()
    {
    }
}
