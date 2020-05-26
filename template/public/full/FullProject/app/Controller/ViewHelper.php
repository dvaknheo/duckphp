<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Controller;

use MY\Base\Helper\ControllerHelper as C;

class ViewHelper
{
    public function index()
    {
        C::Show(get_defined_vars());
    }
}
