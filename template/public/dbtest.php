<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require(__DIR__.'/../../autoload.php');  // @DUCKPHP_HEADFILE

use DuckPhp\DuckPhp;
use DuckPhp\Ext\CallableView;
use DuckPhp\Foundation\SimpleBusinessTrait; // 可变单例模式
use DuckPhp\Foundation\SimpleModelTrait; // 可变单例模式
use DuckPhp\Foundation\Helper; // Helper

//业务类， 还是带上吧。
class MyBusiness
{
    use SimpleBusinessTrait; // 单例模式。
    
    public function getDataList($page, $pagesize)
    {
        return TestModel::getDataList($page, $pagesize);
    }
    public function getData($id)
    {
        return TestModel::getData($id);
    }
    public function addData($data)
    {
        return TestModel::addData($data);
    }
    public function updateData($id, $data)
    {
        return TestModel::updateData($id, $data);
    }
    public function deleteData($id)
    {
        return TestModel::deleteData($id);
    }
}

// 模型类
class TestModel
{
    use SimpleModelTrait;
    public static function getDataList($page, $pagesize)
    {
        $sql = "select * from test order by id desc";
        $total = $this->fetchColumn(M::SqlForCountSimply($sql));
        $list = $this->fetchAll(M::SqlForPager($sql, $page, $pagesize));

        return [$total,$list];
    }
    public static function getData($id)
    {
        $sql = "select * from test where id=?";
        return $this->fetch($sql, $id);
    }
    public static function addData($data)
    {
        $sql = "insert test (content) values(?)";
        $this->execute($sql, $data['content']);
        return Helper::Db()->lastInsertId();
    }
    public static function updateData($id, $data)
    {
        $sql = "update test set content = ? where id=?";
        $flag = $this->execute($sql, $data['content'], $id);
        return $flag;
    }
    public static function deleteData($id)
    {
        $sql = "delete from test where id=? limit 1";
        $this->execute($sql, $id);
    }
}
/////////////////////////////////////////
class MainController
{
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
        Helper::Show302();
    }
}
///////////////
    // 数据库表结构
    
public function CreateSqliteTempTable($options)
{
    $sql = <<<EOT
CREATE TABLE "test" (
	"id"	INTEGER NOT NULL,
	"content"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT)
);
EOT;
    $PDO = new PDO($options['dsn'],$options['username'],$options['password'],);
    $PDO->exec($sql);
}

    // 开始了
    $options = [
        'is_debug' => true,
            // 开启调试模式
        'namespace_controller' => "\\",
            // 设置控制器的命名空间为根 使得 Main 类为入口
            //TODO 安全
        'ext' => [
            CallableView::class => true,
            // 我们用自带扩展 EmptyView 代替系统的 View
        ],
        'callable_view_class' => View::class,
        
        //数据库设置，根据你的需要修改
        'database' => [
            'dsn' => 'sqlite::memory:',
            'username' => null,
            'password' => null,
            'driver_options' => [],
        ],
    ];
    $options['error_404'] = function () {
        (new MainController)->action_index();
    };
    
    $flag = DuckPhp::RunQuickly($options,function(){
        CreateSqliteTempTable(DuckPhp::_()->options);
    });

class View
{
    public static function header($data)
    {
        ?>
<html>
            <head>
            </head>
            <body>
            <header style="border:1px gray solid;">I am Header</header>
<?php
    }
    public static function main_view($data)
    {
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
    public static function show($data)
    {
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
