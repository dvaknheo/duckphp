<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\Business\DemoBusiness;
use LazyToChange\Controller\DefaultAction as C;

class DefaultAction
{
    use SingletonEx;
    use ControllerHelper;
}