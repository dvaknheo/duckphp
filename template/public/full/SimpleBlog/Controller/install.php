<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Controller;

use SimpleBlog\Helper\ControllerHelper  as C;

class install
{
    public function index()
    {
        $database =[
            'host' => '127.0.0.1',
            'port' => '3306',
            'dbname' => 't1',
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
            C::Installer()->install($database);
            $done = true;
        }catch(\Exception $ex){
            $error_message = $ex->getMessage();
            $error_no = $ex->getCode();
            $is_db_error = $error_no === -1;
            $is_write_error = $error_no === -2;
        }
        C::Show(get_defined_vars(),'install');
    }
}
