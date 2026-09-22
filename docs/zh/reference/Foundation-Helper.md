# DuckPhp\Foundation\Helper

## 简介

`Foundation\Helper` 是「四层 Helper 并集」门面：它**自己一个 Helper 方法都不声明**，而是用 `__callStatic` 把调用派发到四个层 Helper 里**第一个声明了该方法**的类，查找顺序固定为：

1. `DuckPhp\Foundation\System\SystemHelper`
2. `DuckPhp\Foundation\Controller\ControllerHelper`
3. `DuckPhp\Foundation\Business\BusinessHelper`
4. `DuckPhp\Foundation\Model\ModelHelper`

需要「一处引入、四层能力都能用」时用它；只想用某一层能力时，请直接用那一层的类（见「相关链接」）。

## 类信息

- 命名空间：`DuckPhp\Foundation`
- 声明：`class Helper`
- 使用的 Trait：无（方法由 `__callStatic` 派发）
- 事件常量：无（10 个 `$EVENT_*` 静态属性住在 Business/Controller 两个层 Helper 上，见「注意事项」）
- 源码另带 **96 条 `@method static …` 注释**（按上面四个类分组，`---- resolved from Foundation\<层>\<层>Helper (n) ----`），只为 IDE / 静态分析可见性，**不是真方法**

## 使用方式

```php
use DuckPhp\Foundation\Helper;

Helper::Setting('app_name');                 // → Controller\ControllerHelper::Setting()
Helper::DbForRead()->fetch($sql);            // → Model\ModelHelper::DbForRead()
Helper::POST('name');                        // → Controller\ControllerHelper::POST()
Helper::addRouteHook($cb, 'prepend-inner');  // → System\SystemHelper::addRouteHook()
Helper::ThrowOn(!$user, '请先登录');          // → System\SystemHelper::ThrowOn()（Project 版语义）
```

## 注意事项

### 12 个跨层重名方法的胜出方

按上面的顺序「首次命中」，因此：

| 重名方法 | 实际派发到 | 说明 |
|---|---|---|
| Setting / AppOptions / Config / XpCall | `Controller\ControllerHelper` | System 没声明 → Controller 先命中；与 Business 版实现等价（都转发到 App/Configer/CoreHelper） |
| FireGlobalEvent / OnGlobalEvent | `System\SystemHelper` | System 先命中；三层实现相同（`GlobalEvent::_()->fire()`） |
| ThrowOn | `System\SystemHelper` | **唯一语义可辨的重名项**：System 版是 Project 版（`CoreHelper::_ProjectThrowOn`），Business/Controller 各有自己的版本 |
| header / setcookie / exit | `System\SystemHelper` | System 先命中；与 Controller 版逐字相同（都走 `SystemWrapper`） |
| AdminService / UserService | `Controller\ControllerHelper` | System 没声明 → Controller 先命中 |

> 历史沿革：早期用「四个 trait + `insteadof`」组合时，`Setting` 一组判给 Business、`header` 一组判给 Controller；改成 `__callStatic` 顺序判定后胜出方变了，但这 8 个方法在竞争层之间**实现等价**，唯一有语义差异的 `ThrowOn` 两种判定都是 System（Project）版。该表由 `tests/Foundation/HelperTest.php` 钉住，改派发顺序即测试变红。

### 其它易错点

- **反射看不到方法**：`method_exists(\DuckPhp\Foundation\Helper::class, 'Db')` 为 `false`，`new ReflectionMethod(...)` 会抛错；要枚举可用方法请查源码里的 `@method` 注释或四个层 Helper。
- **未定义方法**：`__callStatic` 会 `trigger_error("Call to undefined method …", E_USER_ERROR)`。
- 并集类**不再自带** 10 个 `$EVENT_*` 静态属性（早期靠 trait 白拿）：4 个在 `Business\BusinessHelper`、6 个在 `Controller\ControllerHelper`。
- `DuckPhpAllInOne` 用同一套派发（顺序相同），见 [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md)。

## 方法列表

### 公共方法

    public static function __callStatic($method, $args)
按 System → Controller → Business → Model 找第一个 `method_exists` 的层 Helper 并转发；都没有则 `trigger_error(..., E_USER_ERROR)`。

> 96 个可派发方法的签名与说明在四个层 Helper 页里（本类不重复列）。

## 相关链接

- [DuckPhp\Foundation\System\SystemHelper](Foundation-System-SystemHelper.md) — 应用/接线层助手
- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — 控制器层助手
- [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) — 业务层助手
- [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) — 数据层助手（薄壳类）
- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — 数据层助手 trait（`Model\Base` 与 `Model\ModelHelper` 共用）
- [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — 同一套并集派发的「单类应用」版本