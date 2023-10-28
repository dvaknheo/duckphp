<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Helper\AdvanceHelperTrait;
use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\Helper\ControllerHelperTrait;
use DuckPhp\Helper\ModelHelperTrait;

class DuckPhpAllInOne extends DuckPhp
{
    use ModelHelperTrait;
    use BusinessHelperTrait, ControllerHelperTrait, AdvanceHelperTrait{
        BusinessHelperTrait::Setting insteadof ControllerHelperTrait;
        BusinessHelperTrait::Config insteadof ControllerHelperTrait;
        BusinessHelperTrait::XpCall insteadof ControllerHelperTrait;
        BusinessHelperTrait::FireEvent  insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnEvent  insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnEvent  insteadof AdvanceHelperTrait;
        ControllerHelperTrait::header insteadof AdvanceHelperTrait;
        ControllerHelperTrait::setcookie  insteadof AdvanceHelperTrait;
        ControllerHelperTrait::exit  insteadof AdvanceHelperTrait;
    }
}
