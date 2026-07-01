# DuckPhp\DuckPhpAllInOne
[toc]

## 简介

内嵌了所有助手Trait 的总入口。 把所有助手内嵌进入口类以偷懒。

## 依赖关系

- 扩展 [DuckPhp\DuckPhp](DuckPhp.md);
- 应用助手Trait [DuckPhp\Helper\AppHelperTrait](Helper-AppHelperTrait.md);
- 业务助手Trait [DuckPhp\Helper\BusinessHelperTrait](Helper-BusinessHelperTrait.md);
- 控制器助手Trait [DuckPhp\Helper\ControllerHelperTrait](Helper-ControllerHelperTrait.md);
- 模型助手Trait [DuckPhp\Helper\ModelHelperTrait](Helper-ModelHelperTrait.md);

## 选项
    protected function embedMe()

    public function __construct()

    public function onInited()

    public function action_index()

    public function view_head($data)

    public function view_index($data)

    public function view_foot($data)

