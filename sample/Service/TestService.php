<?php
use DNMVCS as DN;
class TestService extends DN\DNService
{
	public function foo()
	{
		return TestModel::G()->foo();
	}
}