<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalAdmin;

use Exception;

class AdminException extends Exception
{
    const CODE_NEED_LOGIN = -1;
    const MESSAGE_NEED_LOGIN = "NEED_LOGIN";
    const CODE_NEED_PERMISSION = -2;
    const MESSAGE_NEED_PEMISSION = "NEED_PERMISSION";

}
