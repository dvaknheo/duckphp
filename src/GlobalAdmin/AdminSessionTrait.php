<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalAdmin;

trait AdminSessionTrait
{
    public function getCurrentAdminId()
    {
        $admin = $this->get('admin', []);
        return $admin['id'] ?? 0;
    }
    public function getCurrentAdminName(): string
    {
        $admin = $this->get('admin', []);
        return $admin['name'] ?? '';
    }

    public function getCurrentAdmin(): array
    {
        return $this->get('admin', []);
    }
    public function setCurrentAdmin(array $admin): void
    {
        $this->set('admin', $admin);
    }

    public function unsetCurrentAdmin(): void
    {
        $this->set('admin', []);
    }
}
