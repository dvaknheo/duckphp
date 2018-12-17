<?php
namespace MY\Model;
use DNMVCS as DN;

class TestModel
{
	use \DNMVCS\DNSingleton;
	
	public function foo()
	{
		return DATE(DATE_ATOM);
	}
}