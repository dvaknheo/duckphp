<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class Supporter extends ComponentBase
{
    public $options = [
        'database_driver_supporter_map' => [
          'mysql' => SupporterByMysql::class,
          'sqlite' => SupporterBySqlite::class,
          ],
          // change.
    ];

    public static function Current()
    {
        return static::_()->getSupporter();
    }
    public function getSupporter(): self
    {
        $driver = DbManager::_()->getDatabaseDriver();
        if (!isset($this->options['database_driver_supporter_map'][$driver])) {
            throw new \Exception("[$driver]  No getSupporter ");
        }
        $new_class = $this->options['database_driver_supporter_map'][$driver];
        return $new_class::_();
    }

    ///////////////
    public function getAllTable(): array
    {
        throw new \Exception('No Impelement');
    }
    public function getSchemeByTable(string $table): string
    {
        throw new \Exception('No Impelement');
    }
}
