<?php
namespace DNMVCS\SwooleHttpd;

trait SimpleHttpd
{
    protected function onHttpRun($request, $response)
    {
        throw new SwooleException("Impelement Me");
    }
    protected function onHttpException($ex)
    {
        throw new SwooleException("Impelement Me");
    }
    protected function onHttpClean()
    {
        throw new SwooleException("Impelement Me");
    }
    
    // en...
    public function initHttp($request, $response)
    {
        SwooleContext::G(new SwooleContext())->initHttp($request, $response);
    }
    public function onRequest($request, $response)
    {
        \defer(function () {
            gc_collect_cycles();
        });
        SwooleCoroutineSingleton::EnableCurrentCoSingleton(); // remark ,here has a defer
        
        \defer(function () {
            $InitObLevel=0;
            for ($i=ob_get_level();$i>$InitObLevel;$i--) {
                ob_end_flush();
            }
            SwooleContext::G()->cleanUp();
        });
        \defer(function () {
            SwooleContext::G()->onShutdown();
        });
        ob_start(function ($str) {
            if (''===$str) {
                return;
            }
            SwooleContext::G()->response->end($str);
        });
        $this->initHttp($request, $response);
        SwooleSuperGlobal::G(new SwooleSuperGlobal()); //TODO
        try {
            $this->onHttpRun($request, $response);
        } catch (\Throwable $ex) {
            $this->onHttpException($ex);
        }
        $this->onHttpClean();
    }
}
