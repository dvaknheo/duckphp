<?php
namespace tests;

use DuckPhp\Component\Lang;
use DuckPhp\Core\App;
use DuckPhp\Core\AutoLoader;
use DuckPhp\Core\Logger;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\SystemWrapper;
use ZThirdDemo\System\MainApp;
use ZThirdDemo\Third\System\ThirdApp;

/**
 * End to end test of the "using a third party app" demo (guide volume 3).
 *
 * It boots the main app once and then drives real requests through serve(),
 * which is how the chapters' examples are verified.
 */
class ZThirdDemoTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $path = __DIR__ . '/data_for_tests/ZThirdDemo/';

        AutoLoader::_()->init([
            'path' => $path . 'src/',
            'namespace' => 'ZThirdDemo',
            'path_namespace' => '',
        ])->run();
        // the third party app lives in its own directory, with its own namespace
        AutoLoader::_()->assignPathNamespace($path . 'third/', 'ZThirdDemo\Third');

        PhaseContainer::RestAllContainerForTesting();
        MainApp::$orders = [];
        MainApp::_(new MainApp())->init(['path' => $path, 'is_debug' => true]);

        ////////////////////////////////////////////////////////////////////
        // 1) the app tree: root phase is "", the mounted app runs in ":shop"
        ////////////////////////////////////////////////////////////////////
        $this->assertSame('', App::Phase());
        $child_phase = App::_()->options['app'][ThirdApp::class]['__phase__'] ?? '';
        $this->assertSame(':shop', $child_phase);
        $this->assertSame('shop/', App::_()->options['app'][ThirdApp::class]['controller_url_prefix']);

        // the root app answers on its own URL
        $this->assertStringContainsString('MAIN-APP-HOME', $this->request('/'));

        ////////////////////////////////////////////////////////////////////
        // 2) cross phase call: switch to the child phase, then come back
        ////////////////////////////////////////////////////////////////////
        $data = json_decode($this->request('/visit'), true);
        $this->assertSame('', $data['phase_before']);
        $this->assertSame(':shop', $data['phase_in_child']);
        $this->assertSame('', $data['phase_after'], 'the phase must be restored');
        $this->assertSame('child-own-config', $data['own_config'], 'the child reads its own config');
        $this->assertSame('parent-greet', $data['greet'], 'the parent shadows the child config file');
        $this->assertSame('Third Party Shop', $data['child_shop_name']);

        ////////////////////////////////////////////////////////////////////
        // 3) proxy call: call into the child phase without leaving this one
        ////////////////////////////////////////////////////////////////////
        $data = json_decode($this->request('/proxy'), true);
        $this->assertSame(2002, $data['ret']['order_id']);
        $this->assertSame('', $data['phase_before']);
        $this->assertSame('', $data['phase_after']);
        // the event fired inside the child phase reached the parent's listener
        $this->assertSame([2002], MainApp::$orders);
        $listeners = json_decode($this->request('/orders'), true);
        $this->assertSame([2002], $listeners['orders']);
        $this->assertArrayHasKey('third.ordered', $listeners['listeners']);

        ////////////////////////////////////////////////////////////////////
        // 4) shared components vs per app components
        ////////////////////////////////////////////////////////////////////
        $root_logger = spl_object_id(Logger::_());
        $root_lang = spl_object_id(Lang::_());
        App::_()->toThisChild(ThirdApp::class);
        $this->assertSame($root_logger, spl_object_id(Logger::_()), 'Logger is public, so it is ONE shared instance');
        $this->assertNotSame($root_lang, spl_object_id(Lang::_()), 'Lang belongs to each app');
        App::Phase('');

        ////////////////////////////////////////////////////////////////////
        // 5) the child app, with the parent's overrides
        ////////////////////////////////////////////////////////////////////
        $out = $this->request('/shop/');
        $this->assertStringContainsString('PARENT-OVERRIDE-VIEW', $out, 'the parent view wins');
        $this->assertStringNotContainsString('CHILD-OWN-VIEW', $out);
        $this->assertStringContainsString('shop=Third Party Shop (overridden)', $out, 'the mapped controller class runs');
        $this->assertStringContainsString('greet_config=parent-greet', $out);
        $this->assertStringContainsString('own_config=child-own-config', $out);

        // a view nobody shadows still comes from the child app itself
        $out = $this->request('/shop/native');
        $this->assertStringContainsString('CHILD-OWN-VIEW native', $out);
        $this->assertStringContainsString('shop=Third Party Shop', $out);

        ////////////////////////////////////////////////////////////////////
        // 6) static resources of both apps, and the resource override
        ////////////////////////////////////////////////////////////////////
        $this->assertStringContainsString('main-app-css', $this->request('/res/main.css'));
        $this->assertStringContainsString(
            'parent-overridden-third-css',
            $this->request('/shop/res/third.css'),
            'res/<child name>/<file> shadows the child resource'
        );
        $this->assertStringContainsString('child-owned-native-css', $this->request('/shop/res/native.css'));

        ////////////////////////////////////////////////////////////////////
        // 7) route level rewrite pointing at the mounted app
        ////////////////////////////////////////////////////////////////////
        $this->assertStringContainsString('PARENT-OVERRIDE-VIEW', $this->request('/legacy-shop'));

        ////////////////////////////////////////////////////////////////////
        // 8) install flow: not installed -> 302 to the install url, then exit
        ////////////////////////////////////////////////////////////////////
        $headers = [];
        $exited = false;
        SystemWrapper::_()->_system_wrapper_replace([
            'header' => function ($output, $replace = true, $code = 0) use (&$headers) {
                $headers[] = (string) $output;
            },
            'exit' => function ($code = 0) use (&$exited) {
                $exited = true;
            },
        ]);
        MainApp::_()->options['installed'] = false;
        $this->assertSame('', App::Phase(), 'still the root phase');
        $this->assertFalse(App::_()->options['installed'], 'App::_() is the main app');
        $out = $this->request('/install');
        // with a real exit the request would stop here; the stub lets it continue
        $redirect = implode("\n", $headers);
        $this->assertStringContainsString('location: ', $redirect);
        $this->assertStringContainsString('/install', $redirect);
        $this->assertTrue($exited, 'the install flow stops the request');
        $this->assertStringContainsString('"installed": true', $out, 'the stub only skips the real exit');

        MainApp::_()->options['installed'] = true;
        SystemWrapper::_()->_system_wrapper_replace(['header' => null, 'exit' => null]);
        $data = json_decode($this->request('/install'), true);
        $this->assertTrue($data['installed']);
    }

    /** drive one request through the root app (it hands it to the child app when needed) */
    private function request(string $path_info): string
    {
        $_SERVER['PATH_INFO'] = $path_info;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];
        $_POST = [];
        ob_start();
        MainApp::_()->serve();
        return (string) ob_get_clean();
    }
}
