<?php
namespace tests_Ext_FinderForController\Controller;
use DuckPhp\Foundation\ControllerTrait;

class MainController
{
    use ControllerTrait;
    public function __construct()
    {
    }
    public function action_index()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function is_not_action()
    {
    }
}