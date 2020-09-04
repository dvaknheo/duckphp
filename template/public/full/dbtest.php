<?php
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE

use App as M;  // Helper 都给我们省掉了
use App as C;  // Helper 都给我们省掉了
use App as V;  // Helper 都给我们省掉了

use DuckPhp\Core\SingletonEx;
use DuckPhp\Ext\EmptyView;

class App extends \DuckPhp\DuckPhp
{
    // @override
    public $options = [
        'is_debug' => true,
            // 开启调试模式
        'skip_setting_file' => true,
            // 本例特殊，跳过设置文件 这个选项防止没有上传设置文件到服务器
        'mode_no_path_info' => true,
            // 单一文件模式
        'namespace_controller'=>"\\",   
            // 设置控制器的命名空间为根 使得 Main 类为入口
        'ext' => [
            EmptyView::class => true,
            // 我们用扩展 EmptyView 代替系统的 View
        ],
        'setting'=>[
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
    public function __construct()
    {
        parent::__construct();
        $this->options['error_404']=function(){(new Main)->index();};
    }
}
//业务类， 还是带上吧。
class MyBusiness
{
    use SingletonEx; // 单例模式。
    
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
    public function updateData($id,$data)
    {
        return TestModel::updateData($id,$data);
    }
    public function deleteData($id)
    {
        return TestModel::deleteData($id);
    }
}

// 模型类
class TestModel
{
    public static function getDataList($page,$pagesize)
    {
        $sql="select * from test order by id desc";
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
        $sql="delete from test where id=? limit 1";
        M::DB()->execute($sql,$id);
    }
}
/////////////////////////////////////////
class Main
{
    public function index()
    {
        list($total,$list)=MyBusiness::G()->getDataList(C::PageNo(),C::PageSize(3));
        $pager=C::PageHtml($total);
        C::Show(get_defined_vars(),'main_view');
    }
    public function do_index()
    {
        MyBusiness::G()->addData($_POST);
        $this->index();
    }
    public function show()
    {
        $data=MyBusiness::G()->getData(C::GET('id',0));
        C::Show(get_defined_vars(),'show');
    }
    public function do_show()
    {
        MyBusiness::G()->updateData(C::POST('id',0),$_POST);
        $this->show();
    }
    public function delete()
    {
        MyBusiness::G()->deleteData(C::GET('id',0));
        C::ExitRouteTo('');
    }
}
///////////////
    // 开始了
        $options = [];


    $flag=App::RunQuickly($options);
    if(!$flag){
        return;
    }
    $data = App::GetViewData();
    extract($data);
    if(empty($skip_head_foot)){
?>
        <html>
            <head>
            </head>
            <body>
            <header style="border:1px gray solid;">I am Header</header>
<?php
    }
    if($view=='main_view'){
?>
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
                <td><a href="<?=__url('delete?id='.$v['id'])?>">删除</a></td>
            </tr>
<?php
        }
?>
        </table>
        <?=$pager?>
        <h1>新增</h1>
        <form method="post" action="<?=__url('')?>">
            <input type="text" name="content">
            <input type="submit">
        </form>
<?php
    }
    if($view=='show'){
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
    if(empty($skip_head_foot)){
        ?>
        <footer style="border:1px gray solid;">I am footer</footer>
    </body>
</html>
<?php
    }
