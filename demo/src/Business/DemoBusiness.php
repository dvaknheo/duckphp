<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Business;

use ProjectNameTemplate\Business\Base;
use ProjectNameTemplate\Business\Helper;
use ProjectNameTemplate\Model\DemoModel;

class DemoBusiness extends Base
{
    public function foo()
    {
        return "<" . DemoModel::_()->foo().">";
    }
    public function getDocData($f)
    {
        $ref = new \ReflectionClass(\DuckPhp\DuckPhp::class);
        $path = realpath(dirname($ref->getFileName()) . '/../docs').'/';
        $file = realpath($path.$f);
        if (substr($file, 0, strlen($path)) != $path) {
            return '';
        }
        $str = file_get_contents($file);
        if (substr($file, -3) === '.md') {
            $str = preg_replace('/([a-z_]+\.gv\.svg)/', "?f=$1", $str); // gv file to md file
        }
        return $str;
    }
    public function testdb()
    {
        return DemoModel::_()->testdb();
    }
}
