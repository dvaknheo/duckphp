<?php
namespace DNMVCS;

trait DNCore_Handler
{
    protected $stop_show_404=false;
    protected $stop_show_exception=false;
    public $beforeShowHandlers=[];
    
    public static function OnBeforeShow($data, $view=null)
    {
        return static::G()->_OnBeforeShow($data, $view);
    }
    public static function On404()
    {
        return static::G()->_On404();
    }
    public static function OnException($ex)
    {
        return static::G()->_OnException($ex);
    }
    public function OnDevErrorHandler($errno, $errstr, $errfile, $errline)
    {
        return static::G()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
    }
    //////////////
    public function toggleStop404Handler($flag=true)
    {
        $this->stop_show_404=$flag;
    }
    public function toggleStopExceptionHandler($flag=true)
    {
        $this->stop_show_exception=$flag;
    }
    
    public function _OnBeforeShow($data, $view=null)
    {
        if ($view===null) {
            DNView::G()->view=DNRoute::G()->getRouteCallingPath();
        }
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
        if ($this->options['skip_view_notice_error']) {
            DNRuntimeState::G()->skipNoticeError();
        }
    }
    public function _On404()
    {
        if ($this->stop_show_404) {
            return;
        }
        
        $error_view=$this->options['error_404'];
        static::header('', true, 404);
        
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($data);
            return;
        }
        if (!$error_view) {
            echo "404 File Not Found\n<!--DNMVCS -->\n";
            return;
        }
        
        $view=DNView::G();
        $view->setViewWrapper(null, null);
        $view->_Show([], $error_view);
        DNRuntimeState::G()->end();
    }
    
    public function _OnException($ex)
    {
        //TODO;
        $flag=DNExceptionManager::G()->checkAndRunErrorHandlers($ex, true);
        if ($flag) {
            return;
        }
        if ($this->stop_show_exception) {
            return;
        }
        static::header('', true, 500);
        $view=DNView::G();
        $data=[];
        $data['is_developing']=static::Developing();
        $data['ex']=$ex;
        $data['message']=$ex->getMessage();
        $data['code']=$ex->getCode();
        $data['trace']=$ex->getTraceAsString();

        $is_error=is_a($ex, 'Error') || is_a($ex, 'ErrorException')?true:false;
        if ($this->options) {
            $error_view=$is_error?$this->options['error_500']:$this->options['error_exception'];
        } else {
            $error_view=null;
        }
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($data);
            return;
        }
        if (!$error_view) {
            $desc=$is_error?'Error':'Exception';
            echo "Internal $desc \n<!--DNMVCS -->\n";
            if ($this->isDev) {
                echo "<hr />";
                echo "\n<pre>Debug On\n\n";
                echo $data['trace'];
                echo "\n</pre>\n";
            }
            return;
        }
        $view->setViewWrapper(null, null);
        $view->_Show($data, $error_view);
        DNRuntimeState::G()->end();
    }
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline)
    {
        //
        if (!$this->isDev) {
            return;
        }
        $descs=array(
            E_USER_NOTICE=>'E_USER_NOTICE',
            E_NOTICE=>'E_NOTICE',
            E_STRICT=>'E_STRICT',
            E_DEPRECATED=>'E_DEPRECATED',
            E_USER_DEPRECATED=>'E_USER_DEPRECATED',
        );
        $error_shortfile=(substr($errfile, 0, strlen($this->path))==$this->path)?substr($errfile, strlen($this->path)):$errfile;
        $data=array(
            'errno'=>$errno,
            'errstr'=>$errstr,
            'errfile'=>$errfile,
            'errline'=>$errline,
            'error_desc'=>$descs[$errno],
            'error_shortfile'=>$error_shortfile,
        );
        $error_view=$this->options['error_debug'];
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($data);
            return;
        }
        if (!$error_view) {
            extract($data);
            echo  <<<EOT
<!--DNMVCS  use view/_sys/error-debug.php to override me -->
<fieldset class="_DNMVC_DEBUG">
	<legend>$error_desc($errno)</legend>
<pre>
{$error_shortfile}:{$errline}
{$errstr}
</pre>
</fieldset>

EOT;
            return;
        }
        DNView::G()->_ShowBlock($error_view, $data);
    }
    public function addBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers[]=$handler;
    }
}
