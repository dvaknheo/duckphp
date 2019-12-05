<?php
namespace MY\Controller;

use MY\Base\Helper\ControllerHelper as C;
class ControllerHelper
{
    public function index()
    {
        C::Show(get_defined_vars());
    }
}