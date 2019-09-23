<?php
namespace DNMVCS\SwooleHttpd;

use DNMVCS\SwooleHttpd\SwooleSingleton;
use SessionHandlerInterface;

class SwooleSessionHandler implements SessionHandlerInterface
{
    use SwooleSingleton;
    private $savePath;
    
    public function open($savePath, $sessionName)
    {
        $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }
        return true;
    }
    public function close()
    {
        return true;
    }
    public function read($id)
    {
        return (string)@file_get_contents("$this->savePath/sess_$id");
    }
    public function write($id, $data)
    {
        return file_put_contents("$this->savePath/sess_$id", $data, LOCK_EX) === false ? false : true;
    }
    public function destroy($id)
    {
        $file = "$this->savePath/sess_$id";
        if (file_exists($file)) {
            unlink($file);
        }
        return true;
    }
    public function gc($maxlifetime)
    {
        $files=glob("$this->savePath/sess_*");
        if (!$files) {
            return true;
        }
        foreach ($files as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }
}
