<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalAdmin;

use DuckPhp\GlobalAdmin\AdminServiceInterface;

interface AdminActionInterface
{
    /**
     * @param bool $check_login
     * @return int|string
     */
    public function id(bool $check_login = true);
    public function name(bool $check_login = true): string;
    /**
     * @param bool $check_login
     * @return array<string, mixed>
     */
    public function data(bool $check_login = true): array;

    /**
     * @return AdminServiceInterface
     */
    public function service();
    /**
     * @return AdminServiceInterface
     */
    public function localService();

    /**
     * @param array<string, mixed> $ext
     */
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string;
    /**
     * @param array<string, mixed> $ext
     */
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string;
    /**
     * @param array<string, mixed> $ext
     */
    public function urlForHome(?string $url_back = null, ?array $ext = null): string;

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $input
     */
    public function mergeViewData(array $input): array;
    /**
     * @param array<string, mixed> $data
     */
    public function _Show(array $data = [], string $view = '');
    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool;
    /**
     * @param array<string, mixed> $ext
     */
    public function log(string $string, ?string $type = null, array $ext = []);


    public function isSuper(): bool;
}
