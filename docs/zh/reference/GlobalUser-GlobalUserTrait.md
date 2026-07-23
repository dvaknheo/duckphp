# DuckPhp\GlobalUser\GlobalUserTrait

用户全局组件 Trait。

## 简介

`GlobalUserTrait` 提供了 `GlobalUser` 类的事件、服务委托和视图融合功能。`GlobalUser` 通过 `use GlobalUserTrait;` 引入这些方法。

## 方法

### 服务委托

| 方法 | 说明 |
|---|---|
| `service()` | 返回用户服务实例。调用 `localService()` 获取本地服务后，通过 `PhaseProxy` 创建跨 Phase 代理 |
| `localService()` | 返回本地用户服务实例。默认抛出 `UserException("No Impelment")`，子类可重写 |

### 事件系统

| 方法 | 说明 |
|---|---|
| `on($event, $callback)` | 注册事件监听。事件名前缀为 `GlobalUser::` |
| `fire($event, ...$args)` | 触发事件 |

### 权限与审计

| 方法 | 说明 |
|---|---|
| `checkAccess($class, $method, $url)` | 检查当前用户权限，委托给 `localService()->doCheckAccess()` |
| `log($string, $type)` | 记录操作日志，委托给 `localService()->doLog()` |
| `batchGetUsernames($ids)` | 批量获取用户名，委托给 `localService()->doBatchGetUsernames()` |

### 视图融合

| 方法 | 说明 |
|---|---|
| `getHeaderFooterData($input): array` | 返回 `['header' => '', 'footer' => '']`，子类可重写 |
| `mergeView($data, $with_set_head_foot, $header, $footer): array` | 融合用户视图数据。在 Phase 间切换获取头尾数据 |

```php
// mergeView 内部逻辑
public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array
{
    $phase = App::Phase();
    $last_phase = App::_()->getLastPhase();

    $user_view = $this->getHeaderFooterData($data);       // 在用户 Phase 下获取头尾
    App::Phase($last_phase);                               // 切回上层 Phase
    $data['user_view'] = $user_view;

    if ($with_set_head_foot) {
        View::_()->setViewHeadFoot($header, $footer);
    }
    App::Phase($phase);
    return $data;
}
```

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md)
