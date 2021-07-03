<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use DuckPhp\Component\Console;
use DuckPhp\DuckPhp;
use DuckPhp\Ext\SqlDumper;

class App extends DuckPhp
{
    //@override
    public $options = [
        // simple_auth_installed = false,
    ];
    protected function onBeforeRun()
    {
        $this->checkInstall($this->options['simple_auth_installed'] ?? false);
    
    protected function checkInstall($flag)
    {
        if(!$flag  && !static::Setting('simple_auth_installed')){
            throw new \ErrorException("SimpleAuth` need install, run install command first. e.g. :`php auth.php SimpleAuth:install`\n");
        }
    }
    //////////////////////
    public function command_install()
    {
        $options = Console::G()->getCliParameters();
        if(count($options)==1 || $options['help']??null){
            echo "Usage: --host=? --port=? --dbname=? --username=? --password=? \n ";
            return;
        }
        $tips = [
            'host' =>'input houst',
            'host' =>'input port',
        ];
        $options['path'] = $this->getPath();
        Installer::G()->install($options);
    }
    protected function getPath()
    {
        return $this->options['path'];
    }
    
    function ReadLines($options,$desc,$validators=[])
    {
    /*
$options =[
    'host' => '127.0.0.1'
];

$tips = <<<'EOT'
host[{host}]
port[{port}]

EOT;
'EOT';
$ret = ReadLines($options,$tips);
var_dump($ret);
    */
        $lines= explode("\n",trim($desc));
        foreach($lines as $line){
            $line = trim($line);
            $flag = preg_match('/\{(.*?)\}/',$line, $m);
            fputs(STDOUT,$line);
            if(!$flag){
                continue;
            }
            $key = $m[1];
            $line = str_replace('{'.$key.'}',$options[$key]??'',$line);
            
            
            $input = trim(fgets(STDIN));
            if($input ===''){
                $input = $options[$key]??'';
            }
            $ret[$key] = $input;
        }
        return $ret;
    }

    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
