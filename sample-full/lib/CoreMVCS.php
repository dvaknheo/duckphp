<?php
class CoreMVCS extends DNMVCS
{
	public function init($path='',$path_common='',$config=array())
	{
		DNView::G(CoreView::G());
		return parent::init($path,$path_common,$config);
	}
}