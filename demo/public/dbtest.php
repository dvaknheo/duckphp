<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//autoload file
$autoload_file = __DIR__.'../vendor/autoload.php';
if (is_file($autoload_file)) {
    require_once $autoload_file;
} else {
    $autoload_file = __DIR__.'/../../vendor/autoload.php';
    if (is_file($autoload_file)) {
        require_once $autoload_file;
    }
}
////////////////////////////////////////

use DuckPhp\DuckPhp;
use DuckPhp\Ext\CallableView;
use DuckPhp\Foundation\SingletonTrait; // the replaceable singleton
use DuckPhp\Foundation\Model\ModelTrait; // the replaceable singleton

use DuckPhp\Foundation\Helper; // Helper

class DbTestApp extends DuckPhp
{
    public $options = [
        'is_debug' => true, // turn on debug mode
        'namespace_controller' => "\\", // controllers live in the root namespace, so Main is the entry class
        'cli_command_prefix'=> 'dbtest', // no namespace here, so give the CLI commands a prefix.

        // this file is also mounted as a child app (`db_test/`), and it is run
        // standalone (`/dbtest.php`) as well: `path` + `lang_default` make both
        // modes find `demo/config/lang-*.php` and the rest of demo's config.
        'path' => __DIR__ . '/../',
        'lang_default' => 'en',
        // the URLs of this app do not carry the `action_` prefix (the root app
        // sets that prefix for its own $options only), so declare it here too;
        // otherwise the welcome route `/db_test/` cannot find `action_index()`.
        'controller_method_prefix' => 'action_',

        'ext' => [
            CallableView::class => true, // the built-in CallableView extension replaces the system View
        ],
        'callable_view_class' => View::class,
        'callable_view_is_object_call' => true,
        
        'local_database' => true,  // a database of its own
        'database' => [
            'dsn' => 'sqlite:runtime/dbtest.sqlite',
            'username' => null,
            'password' => null,
            'driver_options' => [],
        ],
        'error_404'=>[MainController::class,'On404'], // redirect on 404
        
    ];
    public function __construct()
    {
        parent::__construct();
        $dsn = $this->options['database']['dsn'];
        $dsn = "sqlite:". (__DIR__.'/../runtime/dbtest.sqlite');
        //$dsn =str_replace('@runtime@',Helper::getRuntimePath(),$dsn);
        $this->options['database']['dsn'] = $dsn;
    }
}
class MyBusiness
{
    use SingletonTrait;
    public static function On404()
    {
        static::_()->action_index;
    }
    public function getDataList($page, $pagesize)
    {
        return TestModel::_()->getDataList($page, $pagesize);
    }
    public function getData($id)
    {
        return TestModel::_()->getData($id);
    }
    public function addData($data)
    {
        return TestModel::_()->addData($data);
    }
    public function updateData($id, $data)
    {
        return TestModel::_()->updateData($id, $data);
    }
    public function deleteData($id)
    {
        return TestModel::_()->deleteData($id);
    }
    public function install()
    {
        return TestModel::_()->init();
    }
}

// the model class
class TestModel
{
    use ModelTrait;
    public function __construct()
    {
        $this->table_name = 'test';
    }
    public function init()
    {
        $sql = <<<EOT
CREATE TABLE IF NOT EXISTS `'TABLE'` (
	"id"	INTEGER NOT NULL,
	"content"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT)
);
EOT;
        $this->execute($sql);
    }
    public  function getDataList($page, $pagesize)
    {
        $sql = "select * from `'TABLE'` order by id desc";
        $total = $this->fetchColumn(Helper::SqlForCountSimply($sql));
        $list = $this->fetchAll(Helper::SqlForPager($sql, $page, $pagesize));

        return [$total,$list];
    }
    public  function getData($id)
    {
        $sql = "select * from `'TABLE'` where id=?";
        return $this->fetch($sql, (int)$id);
    }
    public function addData($data)
    {
        $sql = "insert into `'TABLE'` (content) values(?)";
        $this->execute($sql, $data['content']);
        return Helper::Db()->lastInsertId();
    }
    public function updateData($id, $data)
    {
        $sql = "update `'TABLE'` set content = ? where id=?";
        $flag = $this->execute($sql, $data['content'], $id);
        return $flag;
    }
    public function deleteData($id)
    {
        $sql = "delete from `'TABLE'` where id=? limit 1";
        $this->execute($sql, $id);
    }
}
/////////////////////////////////////////
class MainController
{
    use SingletonTrait;
    public function __construct()
    {
        //check installed
        MyBusiness::_()->install();
    }
    public function action_index()
    {
        if (Helper::POST()) {
            MyBusiness::_()->addData(Helper::POST());
        }
        list($total, $list) = MyBusiness::_()->getDataList(Helper::PageNo(), Helper::PageWindow(3));
        $pager = Helper::PageHtml($total);
        Helper::Show(get_defined_vars(), 'main_view');
    }
    public function action_show()
    {
        if (Helper::POST()) {
            MyBusiness::_()->updateData(Helper::POST('id', 0), Helper::POST());
        }
        // a hand-typed id that does not exist must not blow up the view
        $data = MyBusiness::_()->getData(Helper::REQUEST('id', 0)) ?: ['id' => 0, 'content' => ''];
        
        Helper::Show(get_defined_vars(), 'show');
    }
    public function action_delete()
    {
        MyBusiness::_()->deleteData(Helper::GET('id', 0));
        Helper::Show302('');
    }
}
///////////////
    // the database table structure
class View
{
    use SingletonTrait;
    public function header($data)
    {
        ?>
<html>
            <head>
            </head>
            <body>
            <header style="border:1px gray solid;">I am Header</header>
<?php
    }
    public function main_view($data)
    {
        extract($data);
        ?>
        <h1><?=__l('dbtest.records')?></h1>
        <table>
            <tr><th>ID</th><th><?=__l('dbtest.content')?></th></tr>
<?php
        foreach ($list as $v) {
            ?>
            <tr>
                <td><?=$v['id']?></td>
                <td><?=__h($v['content'])?></td>
                <td><a href="<?=__url('show?id='.$v['id'])?>"><?=__l('dbtest.edit')?></a></td>
                <td><a href="<?=__url('delete?id='.$v['id'])?>"><?=__l('dbtest.delete')?></a></td>
            </tr>
<?php
        } ?>
        </table>
        <?=$pager?>
        <h1><?=__l('dbtest.add')?></h1>
        <form method="post" action="<?=__url('')?>">
            <input type="text" name="content">
            <input type="submit">
        </form>
<?php
    }
    public function show($data)
    {
        extract($data);
        ?>
        <h1><?=__l('dbtest.view_edit')?></h1>
        <?=__l('dbtest.original')?>
        <p><?=__h($data['content'])?></p>
        <form method="post">
            <input type="hidden" name="id" value="<?=$data['id']?>">
            <input type="text" name="content" value="<?=__h($data['content'])?>">
            <input type="submit" value="<?=__l('dbtest.edit')?>">
        </form>
        <a href="<?=__url('')?>"><?=__l('dbtest.back_home')?></a>

<?php
    }
    public static function foot($data)
    {
        ?>
        <footer style="border:1px gray solid;">I am footer</footer>
    </body>
</html>
<?php
    }
}

// standalone mode: run only when no app has been initialised yet; when this file
// is `require_once`d by demo/src/System/App.php (as a child app) `App::Root()` is
// the demo app, so the guard is false and this entry does not run.
// (`App::Root()` is null before any app is initialised - PHP 8 turns
//  `get_class(null)` into a TypeError, hence the is_object() check.)
$root_app = \DuckPhp\Core\App::Root();
if (!is_object($root_app) || get_class($root_app) === \DuckPhp\Core\App::class){
    DbTestApp::RunQuickly([]);
}
