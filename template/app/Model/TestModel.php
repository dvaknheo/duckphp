<?php
namespace MY\Model;
use MY\Base\Model;

class TestModel extends Model
{	
	public function foo()
	{
		return DATE(DATE_ATOM);
	}
}