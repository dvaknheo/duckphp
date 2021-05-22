<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Controller;

use SimpleBlog\Helper\ControllerHelper  as C;
use SimpleBlog\Business\InstallBusiness;
use SimpleBlog\Business\InstallException;

class install
{
    public function index()
    {
        $database =[
            'host' => '127.0.0.1',
            'port' => '3306',
            'dbname' => 't2',
            'username' => 'admin',
            'password' => '123456',
        ];
        C::Show(get_defined_vars(),'install');
    }
    public function do_index()
    {
        $database = C::POST();

        $done = false;
        try{
            InstallBusiness::G()->install($database);
            $done = true;
        }catch(\Exception $ex){
            $error_message = $ex->getMessage();
            $error_no = $ex->getCode();
            $is_db_error = $error_no === -1;
            $is_write_error = $error_no === -2;
        }
        C::Show(get_defined_vars(),'install');
    }
    /*
    public function dump()
    {
        $ret = [];
        $tables = ['Articles'];
        foreach ($tables as $table) {
            try {
                $sql = "SHOW CREATE TABLE $table";
                $data = DN::DB()->fetch($sql);
                $str = $data['Create Table'];
                $str = preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=1', $str);
                $ret[$table] = $str;
            } catch (\PDOException $ex) {
            }
        }
        var_dump($ret);
        return $ret;
    }
    */
}
