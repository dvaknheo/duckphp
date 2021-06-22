<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Business;

use SimpleBlog\Helper\BusinessHelper as B;
use SimpleBlog\System\App;


class InstallBusiness extends BaseBusiness
{
    //
    public function checkInstall()
    {
        if(App::Setting('simple_blog_installed')){
            return true;
        }
        return false;
    }
    //
    public function install($database)
    {
        return App::install($database);
    }


}