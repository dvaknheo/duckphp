<?php
class UserException extends DNException
{
	public static function OnException($ex)
	{
		$data=array();
		$data['message']=$ex->getMessage();
		$data['code']=$ex->getCode();
		$data['ex']=$ex;
		$data['trace']=$ex->getTraceAsString();
		DNView::Show('_sys/error-exception',$data,false);
		//DNView::G()->
	}
}