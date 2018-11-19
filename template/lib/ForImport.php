<?php
class ForImport
{
	public function foo()
	{
		var_dump(__FILE__,__LINE__);
		var_dump(DATE(DATE_ATOM));
	}
}