<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

trait SystemWrapperTrait
{
    // protected $system_handlers=[];
    public static function system_wrapper_replace(array $funcs)
    {
        return static::G()->_system_wrapper_replace($funcs);
    }
    public static function system_wrapper_get_providers():array
    {
        return static::G()->_system_wrapper_get_providers();
    }
    public function _system_wrapper_replace(array $funcs)
    {
        $this->system_handlers = array_replace($this->system_handlers, $funcs) ?? [];
        return true;
    }
    public function _system_wrapper_get_providers()
    {
        $class = static::class;
        $ret = $this->system_handlers;
        foreach ($ret as $k => &$v) {
            $v = $v ?? [$class,$k];
        }
        unset($v);
        return $ret;
    }
    protected function system_wrapper_call_check($func)
    {
        $func = ltrim($func, '_');
        if (defined('__SYSTEM_WRAPPER_REPLACER')) {
            return is_callable([__SYSTEM_WRAPPER_REPLACER, $func]) ?true:false;
        }
        return isset($this->system_handlers[$func])?true:false;
    }
    protected function system_wrapper_call($func, $input_args)
    {
        $func = ltrim($func, '_');
        if (defined('__SYSTEM_WRAPPER_REPLACER')) {
            // @phpstan-ignore-next-line
            return [__SYSTEM_WRAPPER_REPLACER, $func](...$input_args);
        }
        if (is_callable($this->system_handlers[$func] ?? null)) {
            return ($this->system_handlers[$func])(...$input_args);
        }
        if (!is_callable($func)) {
            throw new \ErrorException("Call to undefined function $func");
        }
        return ($func)(...$input_args);
    }
    ///////////////////////////////////////////
    ///////////
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return static::G()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return static::G()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return static::G()->_exit($code);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return static::G()->_set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return static::G()->_register_shutdown_function($callback, ...$args);
    }
    public static function session_start(array $options = [])
    {
        return static::G()->_session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return static::G()->_session_id($session_id);
    }
    public static function session_destroy()
    {
        return static::G()->_session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return static::G()->_session_set_save_handler($handler);
    }
    public static function mime_content_type($file)
    {
        return static::G()->_mime_content_type($file);
    }
    //////////////
    public function _header($output, bool $replace = true, int $http_response_code = 0)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        ////
        if (PHP_SAPI === 'cli') {
            return;
        }
        // @codeCoverageIgnoreStart
        if (headers_sent()) {
            return;
        }
        header($output, $replace, $http_response_code);
        return;
        // @codeCoverageIgnoreEnd
    }
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            return $this->system_wrapper_call(__FUNCTION__, func_get_args());
        }
        return setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function _exit($code = 0)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            return $this->system_wrapper_call(__FUNCTION__, func_get_args());
        }
        exit($code);        // @codeCoverageIgnore
    }
    public function _set_exception_handler(callable $exception_handler)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            return $this->system_wrapper_call(__FUNCTION__, func_get_args());
        }
        /** @var mixed */
        $handler = $exception_handler; //for phpstan
        return set_exception_handler($handler);
    }
    public function _register_shutdown_function(callable $callback, ...$args)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        register_shutdown_function($callback, ...$args);
    }
    ////[[[[
    public function _session_start(array $options = [])
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        return @session_start($options);
    }
    public function _session_id($session_id = null)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        if (!isset($session_id)) {
            return session_id();
        }
        return @session_id($session_id);  // ???
    }
    public function _session_destroy()
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        return session_destroy();
    }
    public function _session_set_save_handler(\SessionHandlerInterface $handler)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        return session_set_save_handler($handler);
    }
    public function _mime_content_type($file)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            return $this->system_wrapper_call(__FUNCTION__, func_get_args());
        }
        ////
        $mimes = [];
        $mime_string = $this->getMimeData();
        $items = explode("\n", $mime_string);
        foreach ($items as $content) {
            if (\preg_match("/\s*(\S+)\s+(\S.+)/", $content, $match)) {
                $mime_type = $match[1];
                $extension_var = $match[2];
                $extension_array = \explode(' ', \substr($extension_var, 0, -1));
                foreach ($extension_array as $file_extension) {
                    $mimes[$file_extension] = $mime_type;
                }
            }
        }
        return $mimes[pathinfo($file, PATHINFO_EXTENSION)] ?? 'text/plain';
    }
    protected function getMimeData()
    {
        return <<<EOT
types {
    text/html                             html htm shtml;
    text/css                              css;
    text/xml                              xml;
    image/gif                             gif;
    image/jpeg                            jpeg jpg;
    application/javascript                js;
    application/atom+xml                  atom;
    application/rss+xml                   rss;

    text/mathml                           mml;
    text/plain                            txt;
    text/vnd.sun.j2me.app-descriptor      jad;
    text/vnd.wap.wml                      wml;
    text/x-component                      htc;

    image/png                             png;
    image/tiff                            tif tiff;
    image/vnd.wap.wbmp                    wbmp;
    image/x-icon                          ico;
    image/x-jng                           jng;
    image/x-ms-bmp                        bmp;
    image/svg+xml                         svg svgz;
    image/webp                            webp;

    application/font-woff                 woff;
    application/java-archive              jar war ear;
    application/json                      json;
    application/mac-binhex40              hqx;
    application/msword                    doc;
    application/pdf                       pdf;
    application/postscript                ps eps ai;
    application/rtf                       rtf;
    application/vnd.apple.mpegurl         m3u8;
    application/vnd.ms-excel              xls;
    application/vnd.ms-fontobject         eot;
    application/vnd.ms-powerpoint         ppt;
    application/vnd.wap.wmlc              wmlc;
    application/vnd.google-earth.kml+xml  kml;
    application/vnd.google-earth.kmz      kmz;
    application/x-7z-compressed           7z;
    application/x-cocoa                   cco;
    application/x-java-archive-diff       jardiff;
    application/x-java-jnlp-file          jnlp;
    application/x-makeself                run;
    application/x-perl                    pl pm;
    application/x-pilot                   prc pdb;
    application/x-rar-compressed          rar;
    application/x-redhat-package-manager  rpm;
    application/x-sea                     sea;
    application/x-shockwave-flash         swf;
    application/x-stuffit                 sit;
    application/x-tcl                     tcl tk;
    application/x-x509-ca-cert            der pem crt;
    application/x-xpinstall               xpi;
    application/xhtml+xml                 xhtml;
    application/xspf+xml                  xspf;
    application/zip                       zip;

    application/octet-stream              bin exe dll;
    application/octet-stream              deb;
    application/octet-stream              dmg;
    application/octet-stream              iso img;
    application/octet-stream              msi msp msm;

    application/vnd.openxmlformats-officedocument.wordprocessingml.document    docx;
    application/vnd.openxmlformats-officedocument.spreadsheetml.sheet          xlsx;
    application/vnd.openxmlformats-officedocument.presentationml.presentation  pptx;

    audio/midi                            mid midi kar;
    audio/mpeg                            mp3;
    audio/ogg                             ogg;
    audio/x-m4a                           m4a;
    audio/x-realaudio                     ra;

    video/3gpp                            3gpp 3gp;
    video/mp2t                            ts;
    video/mp4                             mp4;
    video/mpeg                            mpeg mpg;
    video/quicktime                       mov;
    video/webm                            webm;
    video/x-flv                           flv;
    video/x-m4v                           m4v;
    video/x-mng                           mng;
    video/x-ms-asf                        asx asf;
    video/x-ms-wmv                        wmv;
    video/x-msvideo                       avi;
    font/ttf                              ttf;
}

EOT;
    }
}
