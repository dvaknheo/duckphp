<?php
namespace MY\Model;
use DNMVCS as DN;

class TestModel extends DN\DNModel
{
	public function foo()
	{
		return DATE(DATE_ATOM);
	}
}