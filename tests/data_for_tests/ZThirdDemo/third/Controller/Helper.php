<?php declare(strict_types=1);
/**
 * ZThirdDemo - helper of the third party app (project side pattern).
 */
namespace ZThirdDemo\Third\Controller;

use DuckPhp\Foundation\SingletonTrait;
use DuckPhp\Helper\ControllerHelperTrait;

class Helper
{
    use ControllerHelperTrait;
    use SingletonTrait;
}
