<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE

use DuckPhp\DuckPhp;
use DuckPhp\DuckPhp as C;  // Helper 都给我们省掉了
use DuckPhp\DuckPhp as M;  // Helper 都给我们省掉了
use DuckPhp\DuckPhp as V;  // Helper 都给我们省掉了
use DuckPhp\Ext\EmptyView;
use DuckPhp\SingletonEx\SingletonExTrait; // 可变单例模式

//业务类， 还是带上吧。
class MyBusiness
{
    use SingletonExTrait; // 单例模式。
    
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
    public static function getDataList($page, $pagesize)
    {
        $sql = "select * from test order by id desc";
        $total = M::Db()->fetchColumn(M::SqlForCountSimply($sql));
        $list = M::Db()->fetchAll(M::SqlForPager($sql, $page, $pagesize));

        return [$total,$list];
    }
    public static function getData($id)
    {
        $sql = "select * from test where id=?";
        return M::Db()->fetch($sql, $id);
    }
    public static function addData($data)
    {
        $sql = "insert test (content) values(?)";
        M::Db()->execute($sql, $data['content']);
        return M::Db()->lastInsertId();
    }
    public static function updateData($id, $data)
    {
        $sql = "update test set content = ? where id=?";
        $flag = M::Db()->execute($sql, $data['content'], $id);
        return $flag;
    }
    public static function deleteData($id)
    {
        $sql = "delete from test where id=? limit 1";
        M::Db()->execute($sql, $id);
    }
}
/////////////////////////////////////////
class Main
{
    public function index()
    {
        list($total, $list) = MyBusiness::G()->getDataList(C::PageNo(), C::PageSize(3));
        $pager = C::PageHtml($total);
        C::Show(get_defined_vars(), 'main_view');
    }
    public function do_index()
    {
        MyBusiness::G()->addData($_POST);
        $this->index();
    }
    public function show()
    {
        $data = MyBusiness::G()->getData(C::GET('id', 0));
        C::Show(get_defined_vars(), 'show');
    }
    public function do_show()
    {
        MyBusiness::G()->updateData(C::POST('id', 0), $_POST);
        $this->show();
    }
    public function delete()
    {
        MyBusiness::G()->deleteData(C::GET('id', 0));
        C::ExitRouteTo('');
    }
}
///////////////
    // 数据库表结构
/*
CREATE TABLE `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `content` varchar(250) NOT NULL COMMENT '内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDb AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4
*/
    // 开始了
    $options = [
        'is_debug' => true,
            // 开启调试模式
        'namespace_controller' => "\\",
            // 设置控制器的命名空间为根 使得 Main 类为入口
        'ext' => [
            EmptyView::class => true,
            // 我们用自带扩展 EmptyView 代替系统的 View
        ],
        'setting' => [
            //数据库设置，根据你的需要修改
            'database_list' => [
                [
                    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8mb4;',
                    'username' => 'admin',
                    'password' => '123456',
                    'driver_options' => [],
                ],
            ],
        ],
    ];
    $options['error_404'] = function () {
        (new Main)->index(); //404 都给我跳转到首页
    }; 
    $flag = DuckPhp::RunQuickly($options);
    $data = DuckPhp::GetViewData();
    
    extract($data);
    if (isset($view_head)) {
        ?>
        <html>
            <head>
            </head>
            <body>
            <header style="border:1px gray solid;">I am Header</header>
<?php
    }
    if ($view == 'main_view') {
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
    if ($view == 'show') {
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
    if (isset($view_foot)) {
        ?>
        <footer style="border:1px gray solid;">I am footer</footer>
    </body>
</html>
<?php
    }
