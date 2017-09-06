<?php
//这个目录是自动加载的目录
class TestClass
{
	public function foo()
	{
		return "TestClass ". DATE(DATE_ATOM);
	}
}