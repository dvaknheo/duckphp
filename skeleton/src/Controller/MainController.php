<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace YourProjectName\Controller;

use YourProjectName\Business\DemoBusiness;

class MainController extends Base
{
    public function __construct()
    {
        $this->init();
    }
    protected function init()
    {
    }
    public function index()
    {
        $var = __h(DemoBusiness::_()->foo());
        Helper::Show(get_defined_vars(), 'main');
    }

}
