<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use SimpleBlog\System\ProjectException;


class NeedInstallException extends ProjectException
{
    const NEED_DATABASE = 1;
    const NEED_INSTALL = 2;
    const NEED_OTHER = 3;
    public function display($ex)
    {
        $code = $ex->getCode();
        if($code == self::NEED_DATABASE){
            App::Show([],'Exception/NeedDatabase');
        }
        if($code == self::NEED_INSTALL){
            App::Show([],'Exception/NeedInstall');
        }
    }
}
