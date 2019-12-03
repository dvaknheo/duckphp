<?php
namespace UUU\Base;
use DNMVCS\DNMVCS as DN;
use DNMVCS\Core\Route;

class App extends \DNMVCS\DNMVCS
{
	public function onInit()
	{
		$this->assignRewrite([
			'~article/(\d+)/?(\d+)?'=>'article?id=$1&page=$2',
		]);
		
		$this->assignRoute([
			'~abc(\d*)'=>function(){var_dump(DN::Parameters());},
		]);
		return parent::onInit();
	}
	public function onRun()
	{
		return parent::onRun();
	}

}