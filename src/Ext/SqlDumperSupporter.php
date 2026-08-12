<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class SqlDumperSupporter extends ComponentBase
{
    public $options = [
        'database_driver_SqlDumperSupporter_map' => [
          'mysql' => SqlDumperSupporterByMysql::class,
          'sqlite' => SqlDumperSupporterBySqlite::class,
          ],
          // change.
    ];

    public static function Current()
    {
        return static::_()->getSqlDumperSupporter();
    }
    public function getSqlDumperSupporter(): self
    {
        $driver = DbManager::_()->getDatabaseDriver();
        if (!isset($this->options['database_driver_SqlDumperSupporter_map'][$driver])) {
            throw new \Exception("[$driver]  No getSqlDumperSupporter ");
        }
        $new_class = $this->options['database_driver_SqlDumperSupporter_map'][$driver];
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
