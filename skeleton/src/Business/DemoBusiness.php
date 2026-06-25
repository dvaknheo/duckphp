<?php declare(strict_types=1);
/**
 * override me
 */
namespace YourProjectName\Business;

use YourProjectName\Model\DemoModel;

class DemoBusiness extends Base
{
    public function foo()
    {
        return "<" . DemoModel::_()->foo().">";
    }
}
