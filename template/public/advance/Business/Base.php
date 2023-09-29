<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Business;

use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\Foundation\SimpleBusinessTrait;

class Base
{
    use SimpleBusinessTrait;
    use BusinessHelperTrait;
	public function __construct()
	{
		//$this->exception_class = BusinessException::class;
	}
}
