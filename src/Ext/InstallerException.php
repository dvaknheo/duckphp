<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use Exception;

class InstallerException extends Exception
{
    const NEED_DATABASE = -1;
    const NEED_INSTALL = -2;
    const NEED_OTHER = -3;
    const INSTALLLED = -4;
    const INSTALLL_LOCK_FAILED = -5;
    const INSTALLL_DATABASE_FAILED = -6;
    
}
