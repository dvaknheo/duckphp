<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Helper\AppHelperTrait;
use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\Helper\ControllerHelperTrait;
use DuckPhp\Helper\ModelHelperTrait;

class DuckPhpAllInOne extends DuckPhp
{
    use ModelHelperTrait;
    use BusinessHelperTrait, ControllerHelperTrait, AppHelperTrait{
        BusinessHelperTrait::Setting insteadof ControllerHelperTrait;
        BusinessHelperTrait::Config insteadof ControllerHelperTrait;
        BusinessHelperTrait::XpCall insteadof ControllerHelperTrait;
        BusinessHelperTrait::FireEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnEvent insteadof AppHelperTrait;
        BusinessHelperTrait::ThrowByFlag insteadof ControllerHelperTrait;
        BusinessHelperTrait::ThrowOn insteadof ControllerHelperTrait;
        ControllerHelperTrait::header insteadof AppHelperTrait;
        ControllerHelperTrait::setcookie  insteadof AppHelperTrait;
        ControllerHelperTrait::exit  insteadof AppHelperTrait;
    }
}
