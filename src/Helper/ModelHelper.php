<?php
namespace DNMVCS\Helper;

use DNMVCS\Core\Helper\ModelHelper as Helper;
use DNMVCS\Core\App as App;

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
