<?php
namespace DNMVCS\Core\Glue;

use DNMVCS\Core\App;

trait GlueRedirect
{
    public static function ExitJson($ret)
    {
        return App::G()->_ExitJson($ret);
    }
    public static function ExitRedirect($url, $only_in_site=true)
    {
        return App::G()->_ExitRedirect($url, $only_in_site);
    }
    public static function ExitRouteTo($url)
    {
        return App::G()->_ExitRedirect(static::URL($url), true);
    }
    public static function Exit404()
    {
        App::On404();
        App::exit_system();
    }
}
