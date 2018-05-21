<?php
class CoreMVCS extends DNMVCS
{
	public function init($path='',$path_common='',$config=array())
	{
		$routes=array(
		'about'=>function(){
			phpinfo();
		}
	);
	DNRoute::G()->mapRoutes($routes);

		DNView::G(CoreView::G());
		return parent::init($path,$path_common,$config);
	}
}