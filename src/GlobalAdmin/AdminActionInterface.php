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

    public function urlForLogin(?string $url_back = null): string;
    public function urlForLogout(): string;

    public function urlForHome(): string;
    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool;
    /**
     * @param array<string, mixed> $ext
     */
    public function log(string $string, ?string $type = null, array $ext = []);

    public function isSuper(): bool;
}
