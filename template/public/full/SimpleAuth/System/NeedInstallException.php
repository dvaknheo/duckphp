<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use SimpleAuth\System\ProjectException;

class NeedInstallException extends ProjectException
{
    public function displayx($ex)
    {
        //App::OnDefaultExetion($ex);
    }
}
