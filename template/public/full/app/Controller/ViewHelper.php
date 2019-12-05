<?php
namespace MY\Controller;

use MY\Base\Helper\ControllerHelper as C;
class ViewHelper
{
    public function index()
    {
        C::Show(get_defined_vars());
    }
}