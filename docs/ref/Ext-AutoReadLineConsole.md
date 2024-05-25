# DuckPhp\Ext\AutoReadLineConsole
[toc]

## 简介
为自动化测试准备的 Console
##方法

    public function autoFill($datas)
datas 是每行以 \n 结尾的数据，代表用户的每个输入

    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
覆盖 父类的 readLines ，fp_in, fp_out 被忽略 TODO fp_in fp_out 存在的时候改为父类的实现
    public function fill($datas)

    public function cleanFill()

    public function toggleLog($flag = true)

    public function getLog()

    protected function deCompileOptions($options, $desc)

