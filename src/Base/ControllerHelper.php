<?php
namespace DNMVCS\Base;

use DNMVCS\Core\Base\ControllerHelper as Helper;
use DNMVCS\Glue\GlueSuperGlobal;
use DNMVCS\Glue\GlueForController;

class ControllerHelper extends Helper
{
    use GlueSuperGlobal;
    use GlueForController;
}
