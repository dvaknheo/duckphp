<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\Console;

trait CommandTrait
{
    public function command_help()
    {
        //echo "Override this to use to show your project routes .\n";
        echo $this->getCommandListInfo();
    }
    ////
    protected function getCommandListInfo()
    {
        $str = '';
        $group = Console::_()->options['cli_command_group'];
        
        foreach ($group as $namespace => $v) {
            if ($namespace === '') {
                $str .= "System default commands:\n";
            } else {
                $str .= "\e[32;7m{$namespace}\033[0m is in phase '{$v['phase']}' power by '{$v['class']}' :\n";
            }
            /////////////////
            $descs = $this->getCommandsByClass($v['class'],$v['method_prefix']);
            foreach ($descs as $method => $desc) {
                $cmd = !$namespace ? $method : $namespace.':'.$method;
                $cmd = "\e[32;1m".str_pad($cmd, 20)."\033[0m";
                $str .= "  $cmd\t$desc\n";
            }
        }
        return $str;
    }
    protected function getCommandsByClass($class, $method_prefix)
    {
        $class = new \ReflectionClass($class);
        $methods = $class->getMethods();
        $ret = [];
        foreach ($methods as $v) {
            $name = $v->getName();
            if (substr($name, 0, strlen($method_prefix)) !== $method_prefix) {
                continue;
            }
            $command = substr($name, strlen($method_prefix));
            $doc = $v->getDocComment();
            
            // first line;
            $desc = ltrim(''.substr(''.$doc, 3));
            $pos = strpos($desc, "\n");
            $pos = ($pos !== false)?$pos:255;
            $desc = trim(substr($desc, 0, $pos), "* \t\n");
            $ret[$command] = $desc;
        }
        return $ret;
    }

}