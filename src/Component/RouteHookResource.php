<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class RouteHookResource extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_override_from' => '',
        'path_resource' => 'res',
        'controller_url_prefix' => '',
        'controller_resource_prefix' => '',
    ];
    public static function Hook($path_info)
    {
        return static::G()->_Hook($path_info);
    }
    protected function initContext(object $context)
    {
        ($this->context_class)::Route()->addRouteHook([static::class,'Hook'], 'append-outter');
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
        
        $options = $this->options;
        $options['path'] = $options['path_override_from']?$options['path_override_from']:$options['path'];
        
        $file = ComponentBase::GetFileFromSubComponent($options, 'resource', $file);
        if (!$file) {
            return false;
        }
        ($this->context_class)::header('Content-Type: '.($this->context_class)::mime_content_type($file));
        echo file_get_contents($file);
        //($this->context_class)::exit();
        return true;
    }
    /////////////////////////////////////////////////////
    public function cloneResource($force = false, &$info = '')
    {
        $flag = preg_match('/^(https?:)?\/\//', $this->options['controller_resource_prefix'] ?? '');
        if ($flag) {
            return;
        }
        $source = realpath(dirname(__DIR__).'/res/') .'/';
        if (static::IsAbsPath($this->options['path_resource'])) {
            $source = static::SlashDir($this->options['path_resource']);
        } else {
            $source = static::SlashDir($this->options['path']).static::SlashDir($this->options['path_resource']);
        }
        
        
        $path_dest = $this->options['controller_resource_prefix'];
        $path_dest = static::IsAbsPath($path_dest) ? $path_dest : $this->options['controller_url_prefix'].$path_dest;
        $path_dest = ltrim($path_dest, '/');
        $document_root = ($this->context_class)::SERVER('DOCUMENT_ROOT', '');
        
        $this->copy_dir($source, $document_root, $path_dest, $force, $info);
    }
    protected function get_dest_dir($path_parent, $path)
    {
        $new_dir = $path_parent;
        $b = explode('/', $path);
        
        foreach ($b as $v) {
            $new_dir .= '/'.$v;
            if (is_dir($new_dir)) {
                continue;
            }
            mkdir($new_dir);
        }
        return $new_dir;
    }
    public function copy_dir($source, $path_parent, $path, $force = false, &$info = '')
    {
        $dest = $this->get_dest_dir($path_parent, $path);
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
