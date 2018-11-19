<?php
namespace DNMVCS;

inteface IRouteHook
{
	public function hook(DNRoute $route);
}