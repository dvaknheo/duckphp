<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com

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
        BusinessHelperTrait::FireGlobalEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnGlobalEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnGlobalEvent insteadof AppHelperTrait;
        BusinessHelperTrait::FireGlobalEvent insteadof AppHelperTrait;
        BusinessHelperTrait::PathOfProject insteadof AppHelperTrait;
        BusinessHelperTrait::PathOfRuntime insteadof AppHelperTrait;
        ControllerHelperTrait::header insteadof AppHelperTrait;
        ControllerHelperTrait::setcookie  insteadof AppHelperTrait;
        ControllerHelperTrait::exit  insteadof AppHelperTrait;
        ControllerHelperTrait::AdminService  insteadof BusinessHelperTrait;
        ControllerHelperTrait::UserService  insteadof BusinessHelperTrait;
    }
}
