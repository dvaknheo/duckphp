<?php
namespace MY\Service;
use DNMVCS as DN;
use MY\Model as M;

class TestService
{
	use \DNMVCS\DNSingleton;
	public function foo()
	{
		//DN\DNMVCS::Import('ForImport');
		//(new \ForImport)->foo();
		//(new \ForAutoload)->foo();
		return M\TestModel::G()->foo();
	}
}