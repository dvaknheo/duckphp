<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use SimpleBlog\System\ProjectException;

class NeedInstallException extends ProjectException
{
    public function displayx($ex)
    {
        
        //App::OnDefaultExetion($ex);        
    }
}
