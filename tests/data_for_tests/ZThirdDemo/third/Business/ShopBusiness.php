<?php declare(strict_types=1);
/**
 * ZThirdDemo - business of the third party app.
 *
 * It only knows its own options and its own config files; the parent app is
 * free to shadow those files from the outside (see chapter 30).
 */
namespace ZThirdDemo\Third\Business;

use DuckPhp\Component\GlobalEvent;

class ShopBusiness extends Base
{
    public function shopName(): string
    {
        return (string) Helper::AppOptions('shop_name');
    }
    /** its own config file, not shadowed by the parent app */
    public function ownConfigWho(): string
    {
        return (string) Helper::Config('third', 'who');
    }
    /** this one IS shadowed by the parent app (config/shop/greet.config.php) */
    public function greetWho(): string
    {
        return (string) Helper::Config('greet', 'who');
    }
    public function placeOrder(int $order_id): array
    {
        // announce it; the parent app listens for this event in its own phase
        GlobalEvent::_()->fire('third.ordered', $order_id);
        return ['order_id' => $order_id, 'shop' => $this->shopName()];
    }
}
