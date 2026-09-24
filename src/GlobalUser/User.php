<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalUser;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\GlobalUser\UserActionInterface;

class User extends ComponentBase implements UserActionInterface
{
    /**
     * @param bool $check_login
     * @return int|string
     */
    public function id(bool $check_login = true)
    {
        throw new DuckPhpSystemException("No GlobalUser Provider.", -1);
    }
    public function name(bool $check_login = true): string
    {
        throw new DuckPhpSystemException("No GlobalUser Provider.", -2);
    }
    public function data(bool $check_login = true): array
    {
        throw new DuckPhpSystemException("Need Provider", -1);
    }
    public function urlForHome(?string $url_back = null, ?array $ext = null): string
    {
        throw new DuckPhpSystemException("Need Provider", -1);
    }
    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
    {
        throw new DuckPhpSystemException("Need Provider", -1);
    }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        throw new DuckPhpSystemException("Need Provider", -1);
    }
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
    {
        throw new DuckPhpSystemException("Need Provider", -1);
    }
    ///////////////
    /**
     * @param array<string, mixed> $data
     */
    public function mergeViewData(array $data): array
    {
        $data['__logined_id'] = $this->id(true);
        $data['__logined_name'] = $this->name(false);
        $data['__logined_data'] = $this->data(false);
        $data['__logined_url_home'] = $this->urlForHome();
        $data['__logined_url_logout'] = $this->urlForLogout();
        return $data;
    }
    ///////////////
    protected function localService()
    {
        throw new DuckPhpSystemException("Need Provider", -1);
    }
    public function service()
    {
        $service = $this->localService();
        return PhaseProxy::CreatePhaseProxy($this->context()->getThisPhaseName(), $service);
    }
    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
    {
        return $this->localService()->canAccess($this->id(), $url, $class, $method);
    }
    /**
     * @param array<string, mixed> $ext
     */
    public function log(string $string, ?string $type = null, array $ext = [])
    {
        return $this->localService()->log($this->id(), $string, $type, $ext);
    }
    /**
     * @param array<string, mixed> $ids
     */
    public function batchGetUsernames(array $ids): array
    {
        return $this->localService()->batchGetUsernames($ids);
    }
}
