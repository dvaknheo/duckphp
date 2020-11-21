<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Controller;

use SimpleBlog\Base\ControllerHelper  as C;

class install
{
    public function index()
    {
        C::Show(get_defined_vars(),'install');
    }
    public function do_index()
    {
        var_dump(C::POST());
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
