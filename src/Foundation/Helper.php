<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp\Foundation;

use DuckPhp\Helper\AppHelperTrait;
use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\Helper\ControllerHelperTrait;
use DuckPhp\Helper\ModelHelperTrait;

class Helper
{
    use ModelHelperTrait;
    use BusinessHelperTrait, ControllerHelperTrait, AppHelperTrait{
        BusinessHelperTrait::Setting insteadof ControllerHelperTrait;
        BusinessHelperTrait::Config insteadof ControllerHelperTrait;
        BusinessHelperTrait::XpCall insteadof ControllerHelperTrait;
        BusinessHelperTrait::FireEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnEvent insteadof AppHelperTrait;
        BusinessHelperTrait::FireEvent insteadof AppHelperTrait;
        BusinessHelperTrait::PathOfProject insteadof AppHelperTrait;
        BusinessHelperTrait::PathOfRuntime insteadof AppHelperTrait;
        ControllerHelperTrait::header insteadof AppHelperTrait;
        ControllerHelperTrait::setcookie  insteadof AppHelperTrait;
        ControllerHelperTrait::exit  insteadof AppHelperTrait;
    }
}
