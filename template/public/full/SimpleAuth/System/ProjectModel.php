<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use DuckPhp\Foundation\SimpleModelTrait;
use DuckPhp\Helper\ModelHelperTrait;
use SimpleAuth\System\App;

class ProjectModel
{
    use SimpleModelTrait;
    use ModelHelperTrait;
    
    public function __construct()
    {
        $this->table_prefix = App::G()->getTablePrefix();
    }
}
