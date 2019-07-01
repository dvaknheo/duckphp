<?php
namespace DNMVCS\Base;

use DNMVCS\Core\Base\ModelHelper as Helper;
use DNMVCS\Ext\DBManager;

class ModelHelper extends Helper
{
    public static function DB($tag=null)
    {
        return DBManager::G()->_DB($tag);
    }
    public static function DB_W()
    {
        return DBManager::G()->_DB_W();
    }
    public static function DB_R()
    {
        return DBManager::G()->_DB_R();
    }
}
