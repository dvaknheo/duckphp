<?php
namespace tests_Ext_RouteLister\Controller;
use DuckPhp\Foundation\SingletonTrait;

class MainController
{
    use SingletonTrait;
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