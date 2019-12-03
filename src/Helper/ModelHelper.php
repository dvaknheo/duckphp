<?php declare(strict_types=1);
namespace DuckPhp\Helper;

use DuckPhp\Core\Helper\ModelHelper as Helper;
use DuckPhp\Core\App as App;

class ModelHelper extends Helper
{
    public static function DB($tag=null)
    {
        return App::G()::DB($tag);
    }
    public static function DB_W()
    {
        return App::G()::DB_W();
    }
    public static function DB_R()
    {
        return App::G()::DB_R();
    }
}
