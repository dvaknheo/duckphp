        'database_driver_supporter_map' => [

          'mysql' => SupporterByMysql::class,

          'sqlite' => SupporterBySqlite::class,

          ],

    public static function Current()

    public function getSupporter()

    public function getInstallDesc()

    public function readDsnSetting($options)

    public function writeDsnSetting($options)

    public function getAllTable()

    public function getSchemeByTable($table)

