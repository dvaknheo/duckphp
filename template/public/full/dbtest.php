<?php
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE
use App as M;
use App as C;
use App as V;

use DuckPhp\Ext\CallableView;
use DuckPhp\Ext\RouteHookOneFileMode;

class App extends \DuckPhp\App
{
    // @override
    protected $options_project = [
        'is_debug' => true,
            // 开启调试模式
        'skip_setting_file' => true,
            // 本例特殊，跳过设置文件 这个选项防止没有上传设置文件到服务器
         
        'ext' => [
            RouteHookOneFileMode::class => true,
                // 开启单一文件模式，服务器不配置也能运行
            CallableView::class => true,
                // 默认的 View 不支持函数调用，我们用扩展 CallableView 代替系统的 View
        ],
        'callable_view_class' => Views::class, 
            // 替换的 View 类。
        'namespace_controller'=>"\\",   
            // 本例特殊，设置控制器的命名空间为根
        'setting'=>[
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
}
class Service
{
    use \DuckPhp\Core\SingletonEx;
    public function getDataList($page,$pagesize)
    {
        return Model::getDataList($page,$pagesize);
    }
    public function getData($id)
    {
        return Model::getData($id);
    }
    public function addData($data)
    {
        return Model::addData($id);

    }
    public function updateData($id,$data)
    {
        return Model::updateData($id);
    }
}
class Model
{
    public static function getDataList($page,$pagesize)
    {
        $sql="select * from test order by id";
        $total=M::DB()->fetchColumn(M::SqlForCountSimply($sql));
        $list=M::DB()->fetchAll(M::SqlForPager($sql,$page,$pagesize));

        return [$total,$list];
    }
    public static function getData($id)
    {
        $sql="select * from test where id=?";
        return M::DB()->fetch($sql,$id);
    }
    public static function addData($data)
    {
        $sql="insert test (content) values(?)";
        M::DB()->execute($sql,$data['content']);
        return M::DB()->lastInsertId();
    }
    public static function updateData($id,$data)
    {
        $sql="update test set content = ? where id=?";
        $flag=M::DB()->execute($sql,$data['content'],$id);
        return $flag;
    }
    public static function deleteData($id)
    {
        $sql="delete form test where id=? limit 1";
        M::DB()->execute($sql,$id);
    }
}
/////////////////////////////////////////
class Main
{
    public function index()
    {
        list($total,$list)=Service::G()->getDataList(C::PageNo(),C::PageSize());
        $pager=C::PageHtml($total);
        C::Show(get_defined_vars(),'main_view');
    }
    public function show()
    {
        Service::G()->getData(C::GET('id'));
        C::Show(get_defined_vars());
    }
    public function do_show()
    {
    }
    public function delete()
    {
        
    }
}

$options=[

];
App::RunQuickly($options);

class Views
{
    public function header($data)
    {
        extract($data); ?>
        <html>
            <head>
            </head>
            <body>
            <header style="border:1px gray solid;">I am Header</header>
<?php
    }

    public function main_view($data)
    {
        extract($data); ?>
        <h1>数据</h1>
        <table>
            <tr><th>ID</th><th>内容</th></tr>
<?php
        foreach($list as $v){
?>
            <tr>
                <td><?=$v['id']?></td>
                <td><?=__h($v['content'])?></td>
                <td><a href="<?=__url('show?id='.$v['id'])?>">编辑</a></td>
            </tr>
<?php
        }
?>
        </table>
        <h1>新增</h1>
        <form method="post">
            <input type="text" name="content">
            <input type="submit">
        </form>
        <?=$pager?>
<?php
    }
    public function show($data)
    {
        extract($data); ?>
        查看/编辑
        <form method="post">
            <input type="text" name="id" value="<?=$data['id']?>">
            <input type="text" name="content" value="<?=__h($data['content'])?>">
            <input type="submit" value="编辑">
        </form>
<?php
    }
    public function footer($data)
    {
        ?>
        <footer style="border:1px gray solid;">I am footer</footer>
    </body>
</html>
<?php
    }
}