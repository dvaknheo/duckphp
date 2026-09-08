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
        AppHelperTrait::ThrowOn insteadof ControllerHelperTrait;
        AppHelperTrait::ThrowOn insteadof BusinessHelperTrait;
        BusinessHelperTrait::Setting insteadof ControllerHelperTrait;
        BusinessHelperTrait::AppOptions insteadof ControllerHelperTrait;
        BusinessHelperTrait::Config insteadof ControllerHelperTrait;
        BusinessHelperTrait::XpCall insteadof ControllerHelperTrait;
        BusinessHelperTrait::FireGlobalEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnGlobalEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnGlobalEvent insteadof AppHelperTrait;
        BusinessHelperTrait::FireGlobalEvent insteadof AppHelperTrait;
        ControllerHelperTrait::header insteadof AppHelperTrait;
        ControllerHelperTrait::setcookie  insteadof AppHelperTrait;
        ControllerHelperTrait::exit  insteadof AppHelperTrait;
        ControllerHelperTrait::AdminService  insteadof BusinessHelperTrait;
        ControllerHelperTrait::UserService  insteadof BusinessHelperTrait;
    }
}
