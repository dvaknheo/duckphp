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
use DuckPhp\Foundation\SimpleBusinessTrait; // 可变单例模式
use DuckPhp\Foundation\SimpleControllerTrait; // 可变单例模式
use DuckPhp\Foundation\SimpleSingletonTrait; // 可变单例模式
use DuckPhp\Foundation\SimpleModelTrait; // 可变单例模式

use DuckPhp\Foundation\Helper; // Helper

class DbTestApp extends DuckPhp
{
    public $options = [
        'is_debug' => true, // 开启调试模式
        'namespace_controller' => "\\", // 设置控制器的命名空间为根 使得 Main 类为入口
        'cli_command_prefix'=> 'dbtest', // 因为我们没命名空间，命令行要设置一下命名空间。
        
        'ext' => [
            CallableView::class => true, // 我们用自带扩展 CallableView 代替系统的 View
        ],
        'callable_view_class' => View::class,
        'callable_view_is_object_call' => true,
        
        'local_database' => true,  // 单独数据库
        'database' => [
            'dsn' => 'sqlite:dbtest.sqlite',
            'username' => null,
            'password' => null,
            'driver_options' => [],
        ],
        'error_404'=>[MainController::class,'On404'], // 404 重新定向
        
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
    use SimpleBusinessTrait;
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

// 模型类
class TestModel
{
    use SimpleModelTrait;
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
    use SimpleControllerTrait;
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
        $data = MyBusiness::_()->getData(Helper::REQUEST('id', 0));
        
        Helper::Show(get_defined_vars(), 'show');
    }
    public function action_delete()
    {
        MyBusiness::_()->deleteData(Helper::GET('id', 0));
        Helper::Show302('');
    }
}
///////////////
    // 数据库表结构
class View
{
    use SimpleSingletonTrait;
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
        <h1>数据</h1>
        <table>
            <tr><th>ID</th><th>内容</th></tr>
<?php
        foreach ($list as $v) {
            ?>
            <tr>
                <td><?=$v['id']?></td>
                <td><?=__h($v['content'])?></td>
                <td><a href="<?=__url('show?id='.$v['id'])?>">编辑</a></td>
                <td><a href="<?=__url('delete?id='.$v['id'])?>">删除</a></td>
            </tr>
<?php
        } ?>
        </table>
        <?=$pager?>
        <h1>新增</h1>
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
        <h1>查看/编辑</h1>
        原内容
        <p><?=__h($data['content'])?></p>
        <form method="post">
            <input type="hidden" name="id" value="<?=$data['id']?>">
            <input type="text" name="content" value="<?=__h($data['content'])?>">
            <input type="submit" value="编辑">
        </form>
        <a href="<?=__url('')?>">回首页</a>

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

if(get_class(\DuckPhp\Core\App::Root())  === \DuckPhp\Core\App::class){
    DbTestApp::RunQuickly([]);
}

