<?php declare(strict_types=1);
/**
 * ZThirdDemo - controller of the main app.
 *
 * Every action here shows one way of talking to the mounted third party app.
 * The actions print JSON so that the demo test can assert on them.
 */
namespace ZThirdDemo\Controller;

use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\Lang;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\Logger;
use ZThirdDemo\System\MainApp;
use ZThirdDemo\Third\Business\ShopBusiness;
use ZThirdDemo\Third\System\ThirdApp;

class MainController extends Base
{
    public function index()
    {
        $child_phase = App::_()->options['app'][ThirdApp::class]['__phase__'] ?? '';
        Helper::Show([
            'root_phase' => App::Phase() === '' ? '(root)' : App::Phase(),
            'child_phase' => $child_phase,
            'shared_logger_is_same' => null !== Logger::_(),
            'lang_instance_id' => spl_object_id(Lang::_()),
            'orders' => MainApp::$orders,
        ], 'main/index');
    }
    /** switch to the child app phase, call it, then switch back */
    public function visit()
    {
        $phase_before = App::Phase();
        $child = App::_()->toThisChild(ThirdApp::class);
        $phase_in_child = App::Phase();
        // this singleton now belongs to the CHILD phase
        $greet = ShopBusiness::_()->greetWho();
        $config = ShopBusiness::_()->ownConfigWho();
        App::Phase($phase_before);
        Helper::ShowJson([
            'phase_before' => $phase_before,
            'phase_in_child' => $phase_in_child,
            'phase_after' => App::Phase(),
            'greet' => $greet,
            'own_config' => $config,
            'child_shop_name' => $child ? $child->options['shop_name'] : null,
        ]);
    }
    /** call into the child phase with a proxy, without leaving the current phase */
    public function proxy()
    {
        $phase_before = App::Phase();
        $child_phase = App::_()->options['app'][ThirdApp::class]['__phase__'];
        $proxy = PhaseProxy::CreatePhaseProxy($child_phase, ShopBusiness::class);
        $ret = $proxy->placeOrder(2002);
        Helper::ShowJson([
            'ret' => $ret,
            'phase_before' => $phase_before,
            'phase_after' => App::Phase(),
        ]);
    }
    /** the parent app listens for an event fired by the child app */
    public function orders()
    {
        Helper::ShowJson([
            'orders' => MainApp::$orders,
            'listeners' => GlobalEvent::_()->all(),
        ]);
    }
    /** chapter 31: leave for the install page while the app is not installed */
    public function install()
    {
        Helper::checkInstall();
        Helper::ShowJson(['installed' => true]);
    }
}
