<?php declare(strict_types=1);
/**
 * ZThirdDemo - class level override of the third party app's controller.
 *
 * It extends the child's own controller, so it can keep the original behavior
 * and only change what it wants. The parent app injects the mapping through the
 * "app" option (controller_class_map), so the child's files stay untouched.
 *
 * It deliberately lives outside Controller/ so that it is not scanned as a
 * controller of the main app.
 */
namespace ZThirdDemo\Override;

use ZThirdDemo\Third\Business\ShopBusiness;
use ZThirdDemo\Third\Controller\Helper;
use ZThirdDemo\Third\Controller\MainController;

class ShopControllerOverride extends MainController
{
    public function index()
    {
        $shop = ShopBusiness::_();
        Helper::Show([
            'shop_name' => $shop->shopName() . ' (overridden)',
            'own_config' => $shop->ownConfigWho(),
            'greet_config' => $shop->greetWho(),
        ], 'index');
    }
}
