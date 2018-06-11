<?php
class TestModel extends DNModel
{
	public function foo()
	{
		return DATE(DATE_ATOM);
	}
}