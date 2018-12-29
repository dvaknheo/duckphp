<?php
namespace MY\Model;

class TestModel
{
	use \DNMVCS\DNSingleton;
	
	public function foo()
	{
		return DATE(DATE_ATOM);
	}
}