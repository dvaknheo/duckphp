<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\SingletonTrait;

class AdminSystem
{
    use SingletonTrait;
    public static function CallInPhase($phase)
    {
        return new PhaseProxy($phase, static::class);
    }
    
    protected $data;

    public function current()
    {
        //GlobalAdmin::(MyUser::CallInPhase(Stxx));
        //Admin($new) , AdminId(),AdminData(),
        // $this->data = $foo;
        //return $this;
    }
    public function id()
    {
        $this->checkLogin();
        return $this->data['id'];
    }
    public function data()
    {
        $this->checkLogin();
        return $this->data;
    }
    public function isSuper()
    {
        $this->checkLogin();
        
    }
    public function canAccess()
    {
        //$this->checkLogin();
    }
    protected function checkLogin()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
        //$this->data =[];
    }
    //////////////////////
    public function urlForRegist($url_back = null, $ext = null)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function urlForLogin($url_back = null, $ext = null)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function urlForHome($url_back = null, $ext = null)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    //////////////////////
    public function regist($post)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function login($post)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function logout()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
}
