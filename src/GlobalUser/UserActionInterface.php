<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

interface UserActionInterface
{
    /**
     * @param bool $check_login
     * @return int|string
     */
    public function id(bool $check_login = true);
    public function name(bool $check_login = true): string;
    public function data(bool $check_login = true): array;

    /**
     * @return UserServiceInterface
     */
    public function service();
    /**
     * @return UserServiceInterface
     */
    public function localService();

    public function urlForRegist(?string $url_back = null, ?array $ext = null): string;
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string;
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string;
    public function urlForHome(?string $url_back = null, ?array $ext = null): string;

    public function mergeViewData(array $input): array;

    public function checkAccess(string $class, string $method, ?string $url = null);
    public function log(string $string, ?string $type = null, array $ext = []);
    
    public function batchGetUsernames(array $ids): array;
}
