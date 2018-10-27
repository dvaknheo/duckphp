<?php
namespace DNMVCS;
use SuperGlobal;

class SwooleSessionHandler extends \SessionHandler
{
	use DNSingleton;
	
	public static function Init()
	{
		ini_set('session.use_cookies',0);
		session_set_save_handler(self::G(),false);
		DNSwooleHttpServer::register_shutdown_function([self::G(),'writeClose']);
	}
	public function writeClose()
	{
		$session_id=session_id();
		
		$this->write($session_id,'');
		$this->close();
	}
	public function open($savePath,$sessionName)
	{
		$ret=parent::open($savePath, $sessionName);
		
		$session_id=SuperGlobal\COOKIE::Get($sessionName);
		
		if($session_id===null || ! preg_match('/[a-zA-Z0-9,-]+/',$session_id)){
			$session_id=$this->create_sid();
		}
		session_id($session_id);
		
		////
		DNSwooleHttpServer::setcookie($sessionName,$session_id
			,time()+ini_get('session.cookie_lifetime')
			,ini_get('session.cookie_path')
			,ini_get('session.cookie_domain')
			,ini_get('session.cookie_secure')
			,ini_get('session.cookie_httponly')
		);
		
		return $ret;
	}
	
	public function read($session_id)
	{
		$ret=parent::read($session_id);
		$data=session_decode($ret);
		
		foreach($data as $k=>$v){
			SuperGlobal\SESSION::Set($k,$v);
		}
		return session_encode([]);
	}

	public function write( $session_id, $session_data)
	{
		$session_data=session_encode(SuperGlobal\SESSION::All());
		$ret=parent::write($session_id,$session_data);
		
		return $ret;
	}
	public function destroy($session_id)
	{
		$ret=parent::destroy($session_id);
		
		$sessionName=session_name();
		DNSwooleHttpServer::setcookie($sessionName,$session_id,strtotime('-30 days'));
		
		return $ret;
	}
}
