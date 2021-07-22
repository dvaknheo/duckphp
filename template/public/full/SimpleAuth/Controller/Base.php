<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Controller;

use SimpleAuth\System\ProjectController;

class Base extends ProjectController
{
    public function __construct()
    {
        parent::__construct();
        if(method_exists(self::class, self::getRouteCallingMethod())){
            self::Exit404();
        }
    }
}