<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;
use DuckPhp\Core\SystemWrapper;

class RouteHookResource extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_resource' => 'res',
        'path_document' => 'public',
        'controller_url_prefix' => '',
        'controller_resource_prefix' => '',
    ];
    public static function Hook($path_info)
    {
        return static::_()->_Hook($path_info);
    }
    protected function initContext(object $context)
    {
        if ($this->options['controller_resource_prefix']) {
            Route::_()->addRouteHook([static::class,'Hook'], 'append-outter');
        }
        return $this;
    }
    public function _Hook($path_info)
    {
        $file = urldecode(''.$path_info);
        $prefix = $this->options['controller_url_prefix'];
        $prefix = $prefix?'/'.$prefix:'';
        $prefix .= $this->options['controller_resource_prefix'];
        if (!empty($prefix) && (substr($file, 0, strlen($prefix)) !== $prefix)) {
            return false;
        }
        
        if (!empty($prefix)) {
            $file = substr($file, strlen($prefix));
        }
        if (false !== strpos($file, '../')) {
            return false;
        }
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            return false;
        }
        
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_resource'], $file);
        if (!is_file($full_file)) {
            return false;
        }
        //$etag = md5(filemtime($full_file));
        SystemWrapper::header('Content-Type: '. SystemWrapper::mime_content_type($full_file));
        
        //SystemWrapper::header('Etag: '. $etag);
        
        //if (($etag ===($_SERVER['HTTP_IF_NONE_MATCH']?? ''))) {
        //    SystemWrapper::header('done',true,304);
        //    return true;
        //}
        echo file_get_contents($full_file);
        return true;
    }
    /////////////////////////////////////////////////////
    public function cloneResource($force = false, &$info = '')
    {
        if (!$this->options['controller_resource_prefix']) {
            return;
        }
        $controller_resource_prefix = $this->options['controller_resource_prefix'];
        $controller_resource_prefix = ($controller_resource_prefix === './') ? '' : $controller_resource_prefix;
        
        $flag = preg_match('/^(https?:)?\/\//', $controller_resource_prefix ?? '');
        if ($flag) {
            return;
        }
        $source = $this->extendFullFile($this->options['path'], $this->options['path_resource'], '', false);
        $source = realpath($source);
        if (!$source) {
            return;
        }
        
        //for console.
        
        $phase = App::Phase(App::Root()->getOverridingClass());
        $document_root = App::Root()->extendFullFile(App::Root()->options['path'], App::Root()->options['path_document'] ?? 'public', '', false);
        App::Phase($phase);
        
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $_SERVER['DOCUMENT_ROOT'] = '';
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        
        Route::_()->options['controller_resource_prefix'] = $this->options['controller_resource_prefix'];
        $path_dest = Route::_()->_Res('');
        
        $dest = $this->get_dest_dir($document_root, $path_dest);
        
        $this->copy_dir($source, $dest, $force, $info);
    }
    protected function get_dest_dir($path_parent, $path)
    {
        $new_dir = rtrim($path_parent, '/');
        $b = explode('/', trim($path, '/'));
        
        foreach ($b as $v) {
            $new_dir .= '/'.$v;
            if (is_dir($new_dir)) {
                continue;
            }
            @mkdir($new_dir);
        }
        return $new_dir;
    }
    protected function copy_dir($source, $dest, $force = false, &$info = '')
    {
        $source = rtrim(''.realpath($source), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $dest = rtrim(''.realpath($dest), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $t_files = \iterator_to_array($iterator, false);
        $files = [];
        
        foreach ($t_files as $file) {
            $short_file_name = substr($file, strlen($source));
            $files[$file] = $short_file_name;
        }
        
        if (!$force) {
            $flag = $this->check_files_exist($source, $dest, $files, $info);
            if ($flag) {
                $info .= "File Exsits!\n";
                return;
            }
        }
        $info .= "Copying file...\n";
        
        $flag = $this->create_directories($dest, $files, $info);
        if (!$flag) {
            return; // @codeCoverageIgnore
        }
        $is_in_full = false;
        
        foreach ($files as $file => $short_file_name) {
            $dest_file = $dest.$short_file_name;
            $data = file_get_contents(''.$file);
            $flag = file_put_contents($dest_file, $data);
            
            $info .= $dest_file."\n";
            //decoct(fileperms($file) & 0777);
        }
        //echo  "\nDone.\n";
    }
    protected function check_files_exist($source, $dest, $files, &$info)
    {
        foreach ($files as $file => $short_file_name) {
            $dest_file = $dest.$short_file_name;
            if (is_file($dest_file)) {
                $info .= "file exists: $dest_file \n";
                return true;
            }
        }
        return false;
    }
    protected function create_directories($dest, $files, &$info)
    {
        foreach ($files as $file => $short_file_name) {
            // mkdir.
            $blocks = explode(DIRECTORY_SEPARATOR, $short_file_name);
            array_pop($blocks);
            $full_dir = $dest;
            foreach ($blocks as $t) {
                $full_dir .= DIRECTORY_SEPARATOR.$t;
                if (!is_dir($full_dir)) {
                    try {
                        $flag = mkdir($full_dir);
                    } catch (\Throwable $ex) {                               // @codeCoverageIgnore
                        $info .= "create file failed: $full_dir \n";// @codeCoverageIgnore
                        return false;   // @codeCoverageIgnore
                    }
                }
            }
        }
        return true;
    }
}
