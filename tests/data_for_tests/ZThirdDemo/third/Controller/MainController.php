<?php declare(strict_types=1);
/**
 * ZThirdDemo - controller of the third party app.
 *
 * "index" renders the view named "index", which the parent app shadows with
 * its own file; "native" renders a view nobody shadows, to show the fallback.
 */
namespace ZThirdDemo\Third\Controller;

use ZThirdDemo\Third\Business\ShopBusiness;

class MainController extends Base
{
    public function index()
    {
        $shop = ShopBusiness::_();
        Helper::Show([
            'shop_name' => $shop->shopName(),
            'own_config' => $shop->ownConfigWho(),
            'greet_config' => $shop->greetWho(),
        ], 'index');
    }
    public function native()
    {
        Helper::Show(['shop_name' => ShopBusiness::_()->shopName()], 'native');
    }
    public function order()
    {
        $ret = ShopBusiness::_()->placeOrder((int) Helper::GET('id', 1));
        Helper::ShowJson($ret);
    }
}
