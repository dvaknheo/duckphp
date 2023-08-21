<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Model;

use DuckPhp\Component\SimpleModelTrait;
use DuckPhp\Helper\ModelHelperTrait;
use DuckPhp\SingletonEx\SingletonExTrait;

use LazyToChange\System\ProjectModel;

class BaseModel
{
    use SimpleModelTrait;
    use ModelHelperTrait;
    use SingletonexTrait;
}
