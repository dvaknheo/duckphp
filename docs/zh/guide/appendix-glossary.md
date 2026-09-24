# 附录 A · 术语表

> 这是**全书用词基准**。正文里同一个概念只用这里定下的叫法；首次出现时给出英文与对应的代码实体，之后不再重复解释。
> 术语后面括号里是它在哪一章展开。

## 应用与相位

| 术语             | 英文 / 代码实体                                                                                         | 含义                                                                                                   |
| -------------- | ------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| **应用**         | [App](../reference/Core-App.md) / `DuckPhp\Core\App` 的子类实例                                        | 一个可初始化的运行单元：有自己的命名空间、项目路径、选项与容器。获取实例一律用 `App::_()` 而不是 `new`。                                        |
| **入口类**        | Entry / [`DuckPhp`](../reference/DuckPhp.md)、[`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) | 工程里实际被 `RunQuickly()` 的那个应用类，通常放在 `src/System/App.php`。（第 1-2 章）                                     |
| **根应用**        | Root App / `App::Root()`                                                                          | 应用树最上层的那一个，相位名为空字符串 `''`。`App::Root(true)` 可「取根实例并把当前相位切回根」。                                         |
| **子应用**        | Child App / `app` 选项                                                                              | 通过 `'app' => [子应用类 => [...]]` 挂在父应用下的应用；有独立的相位与容器，可带自己的路由前缀、视图、配置。（第 3-1 章）                          |
| **相位**         | Phase / `App::Phase()`                                                                            | 「实例空间」：同一进程里按相位分桶存放单例，所以同名类在不同相位下是不同实例。`::_()` 永远返回**当前相位**里的那个。根相位是 `''`，子相位命名形如 `父:子`。（第 8、26 章）   |
| **相位切换**       | `App::Phase($name)` / `toThisChild()` / `FromCurrentParent()` / `SwitchRootPhase()`               | 进入某个应用/回到父/回到根的操作。它们**改变当前相位**，不带参数时 `Phase()` 只读取。（第 3-1 章）                                         |
| **实例容器**       | [PhaseContainer](../reference/Core-PhaseContainer.md)                                             | 保存所有单例的容器：按相位分桶，另有 `#public` 共享桶。（第 4-1 章）                                                           |
| **共享容器 / 公共类** | publics / `#public` 桶 / `EXT_FOLLOW_APP`                                                          | 标记为 public 的实例在所有相位下**共用一个**（如根应用、[Console](../reference/Core-Console.md)、路由），是「组件共享」的实现基础。（第 3-4 章） |
| **局部对象**       | `createLocalObject()`                                                                             | 强制在当前相位新建一份实例（不共享），用于想让子应用各用一套组件的场景。（第 3-4 章）                                                        |

## URL 与资源

| 术语               | 英文 / 代码实体                                                                                                    | 含义                                                    |
| ---------------- | ------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------- |
| **挂载前缀**         | `controller_url_prefix`                                                                                      | 一个应用所有控制器 URL 的前缀；子应用也用它实现「挂到某个路径下」。（第 3-2 章）         |
| **文档根**          | `path_document`                                                                                              | Web 服务器暴露的目录（如 `public/`）。决定 URL 里哪些路径由 Web 服务器直接吐文件。 |
| **资源目录**         | `path_resource` / [`RouteHookResource`](../reference/Component-RouteHookResource.md)                         | 不做 rewrite 的部署环境下，由框架代发静态资源的目录。（第 3-3 章）              |
| **重写**           | Rewrite / [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md)                                   | 把某个 URL 映射到另一个路由（不改变用户看到的地址）。（第 2-3 章）                |
| **路由映射**         | [Route](../reference/Core-Route.md) Map / [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) | 把 URL 直接绑到「类@方法」，可标为「重要路由」优先匹配。（第 2-3 章）              |
| **PATH_INFO 兼容** | [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md)                               | 无 PATH_INFO 的服务器上用查询串传递路由。（第 2-3 章）                   |

## 分层与命名

| 术语         | 英文 / 代码实体                                                         | 含义                                                   |
| ---------- | ----------------------------------------------------------------- | ---------------------------------------------------- |
| **四层**     | Controller / Business / Model / [View](../reference/Core-View.md) | 单向调用：控制器收输入出输出、业务放逻辑（无状态）、模型只做数据访问、视图只做显示。（第 2-1 章）  |
| **系统层**    | System                                                            | 放应用配置与框架相关的接线，命名空间 `src/System`。（第 1-3 章）            |
| **动作类**    | Action                                                            | 控制器层里可复用的无状态类，供多个控制器共享。（第 2-5 章）                     |
| **服务类**    | Service                                                           | 业务层里可复用的类，供多个 Business 共享。（第 2-1 章）                  |
| **Helper** | `DuckPhp\Foundation\<层>\<层>Helper` / 工程的 `Helper`                 | 分层助手：`Helper::Show()` 等便捷入口，替代到处 `use` 框架类。（第 2-9 章） |

| **控制器后缀 / 方法前缀** | `controller_class_postfix` / `controller_method_prefix` | 决定 URL 与类名、方法名之间的换算；方法前缀默认为空。（第 9、10 章） |
| **欢迎页** | `controller_welcome_class` / `_method` | 默认 `Main::index`：根路径与单段路径都先落到它。（第 2-3 章） |

## 组件与扩展

| 术语       | 英文 / 代码实体                                          | 含义                                                                                                                                                                |
| -------- | -------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **组件**   | Component / `DuckPhp\Component\*`                  | 框架自带的能力单元（[DbManager](../reference/Component-DbManager.md)、[Cache](../reference/Component-Cache.md)、[Lang](../reference/Component-Lang.md)……），由应用初始化时装载。（第 4-2 章） |
| **扩展**   | Ext / `DuckPhp\Ext\*`                              | 按需挂上的可选能力（JsonRpc、中间件、权限菜单……），通过 `ext` 选项声明。（第 4-2 章）                                                                                                             |
| **装载模式** | `EXT_*` 常量                                         | `EXT_DEFAULT`/`EXT_FOLLOW_APP`/`EXT_SKIP_INIT`/`EXT_RENEW`/`EXT_DISABLE`，决定扩展怎么被初始化与是否共享。（第 29、34 章）                                                              |
| **覆盖**   | Override                                           | 用文件、类、路由、视图、组件五个层次改写已有行为的总称。（第 3-5 章）                                                                                                                             |
| **覆盖类**  | `override_class` / `override_from`                 | 让另一个类接管当前应用的初始化，常用于「不改第三方代码而换掉它的 App 类」。（第 30、35 章）                                                                                                               |
| **相位代理** | [PhaseProxy](../reference/Component-PhaseProxy.md) | 用一个代理对象把调用固定到某个相位上执行，实现「跨应用调用」。（第 3-4 章）                                                                                                                          |

## 配置与数据

| 术语        | 英文 / 代码实体                                                               | 含义                                                                                       |
| --------- | ----------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| **选项**    | options / `$options`                                                    | 传给应用或组件的配置数组，有白名单与默认值；`App::_()->options` 可读。（第 1-5 章）                                   |
| **设置**    | setting / `Setting()`                                                   | 来自配置文件（`DuckPhpSettings.config.php`、`.env`）的键值，通常放密码、环境相关项。（第 1-5 章）                     |
| **可覆盖文件** | `getOverrideableFile()`                                                 | 按相位逐层回退查找文件：子应用可在自己的目录里「覆盖」父应用的同名视图/配置。（第 3-5 章）                                         |
| **运行时目录** | `path_runtime`                                                          | 日志、缓存等可写目录。                                                                              |
| **配置目录**  | `path_config`                                                           | `config/`，[`Configer`](../reference/Component-Configer.md) 与安装器从这里读文件。                   |
| **事件**    | Event / [`GlobalEvent`](../reference/Component-GlobalEvent.md)          | 跨应用广播的命名事件；事件名用 `registering`/`registered` 这类「进行中/已完成」后缀。（第 2-13 章）                      |
| **条件抛**   | `ThrowOn()` / `*ThrowOn()`                                              | 「满足条件就抛」的守卫式写法，来自 [`ThrowOnTrait`](../reference/Ext-ThrowOnTrait.md) 或 Helper。（第 2-12 章） |
| **系统异常**  | [`DuckPhpSystemException`](../reference/Core-DuckPhpSystemException.md) | **只**表示「框架自己出问题」，工程的业务/权限异常请直接继承 `\Exception`。（第 2-12 章）                                 |

## 写作约定

- 提到文件路径时写**仓库内相对路径**；提到类时写**全限定名**（首次出现）或**短名**（同章内后续）。
- 示例代码里的 `你的项目命名空间` 统一写作 `MyProj`；示例工程名统一写作 `ZThirdDemo`（第三卷）或 `ZAllDemo`（第二卷）。
- 「应用」与「子应用」不要混用「App 实例」「子 App」这类随意叫法；「相位」不写作「阶段」。
