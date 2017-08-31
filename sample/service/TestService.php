<?php
class TestService extends DNService
{
	public function foo()
	{
		return TestModel::G()->foo();
	}
}