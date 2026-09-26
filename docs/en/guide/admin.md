# 2-20 Using the admin system

> What this solves: answering "who is this admin", "may they do this" and "are they a super admin" inside your back-office controllers and business code, plus how the back-office menu is generated.
> Prerequisites: [Chapter 2-19 Using the user system](user.md) (the two entry sets are parallel; this chapter only covers what differs), [Chapter 2-5 Controllers](controllers.md), [Chapter 2-11 Sessions](session.md). About 20 minutes.
> This chapter covers **usage only**; for "how the admin system gets wired in" (three implementations + options + the `ext` mount) see [Chapter 4-12 Implementing the admin system](impl-admin.md). **Before it is wired**, these entry points throw `DuckPhpSystemException` outright.
> Runnable assets: `tests/Foundation/Controller/AdminControllerBaseTest.php`, `tests/GlobalAdmin/GlobalAdminTest.php`.

## Minimal example

```php
// MyProj\Controller\AdminController
public function dashboard()
{
    $adminId = Helper::AdminId();        // the current admin id; not logged in -> 302 to the back-office login page and the request ends
    $name    = Helper::AdminName();      // the current admin name
    $isSuper = Helper::Admin()->isSuper();

    Helper::Admin()->log('opened the dashboard', 'dashboard');   // an action log, landing in your AdminServiceInterface::log()
    Helper::Show(get_defined_vars(), 'admin/dashboard');
}
```

The business layer cannot see "who is logged in", but it can reach the **admin service**:

```php
// MyProj\Business\AdminReportService
public function export(int $adminId): array
{
    Helper::AdminService()->log($adminId, 'exported a back-office report', 'export', ['rows' => 120]);
    return ReportModel::_()->all();
}
```

## How it works

### 1. The entry table (matching the user side item by item)

| Which layer you are in | Entry point | What you get |
|---|---|---|
| Controller | `Helper::Admin()` | [`AdminActionInterface`](../reference/GlobalAdmin-AdminActionInterface.md) (the current admin object) |
| Controller | `Helper::AdminId()` / `Helper::AdminName()` | The current admin id / name |
| Controller | `Helper::AdminService()` | [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md) |
| Business | `Helper::AdminService()` | The same thing — **the same service** |
| Anywhere | [`Admin::_()`](../reference/GlobalAdmin-Admin.md) | The current admin object (`Helper::Admin()` is exactly this) |

What `Helper::Admin()` can do (parallel to the table in [Chapter 2-19 §2](user.md); the admin side has no registration but has `isSuper()`):

| Method | Returns | Notes |
|---|---|---|
| `id(bool $check_login = true)` / `name(...)` / `data(...)` | id / name / array | The current admin; for "not logged in" see §2 |
| `canAccess(?string $url = null, ?string $class = null, ?string $method = null)` | `bool` | The permission test; with no arguments it uses the current route; not logged in is simply `false` |
| `isSuper()` | `bool` | Whether this is a super admin (admin side only) |
| `log(string $string, ?string $type = null, array $ext = [])` | — | Records one action-log entry |
| `urlForHome()` / `urlForLogin($url_back = null)` / `urlForLogout()` | `string` | Back-office URLs (**there is no `urlForRegister()`**) |
| `service()` | `AdminServiceInterface` | Equivalent to `Helper::AdminService()` |

### 2. What happens when nobody is logged in

Exactly as on the user side ([Chapter 2-19 §3](user.md)): `id()` / `name()` / `data()` default to `$check_login = true`, and when nobody is logged in one of three things happens — with `globaladmin_need_login_callback` configured the callback runs; a non-Ajax request gets a `302` to `urlForLogin(current path)`; an Ajax request gets `{"error_code":-1,"error_message":"NEED_LOGIN"}` — and **all three paths end the request**. To check for yourself, pass `false`:

```php
$adminId = Helper::AdminId(false);       // 0 when nobody is logged in
```

### 3. The back-office controller fallback: `AdminControllerBase`

A back-office controller usually extends [`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md), which does three things for you:

1. `checkInstall(null)`: if the app is not installed, `302` to the install page and stop ([Chapter 3-6](installer.md));
2. `Helper::AdminId(true)`: "not logged in" is handled as in the previous section;
3. `Helper::Admin()->canAccess()`: **when there is no permission it calls `onNeedPermission()`** (default: non-Ajax `302` to the login page, Ajax outputs `{"error_code":-1,"error_message":"NEED_PERMISSION"}`), then ends the request.

To change what "no permission" looks like (a 403 JSON, a logged violation, …), **override `onNeedPermission()`** (no arguments) in your own controller base class:

```php
class AdminBaseController extends AdminControllerBase
{
    protected function onNeedPermission()
    {
        Helper::ShowJson(['error_code' => -2, 'error_message' => 'NEED_PERMISSION']);
    }
}
```

It also sets `__use_logined_view_data` and `__use_logined_header_footer_file` to true ([Chapter 2-19 §6](user.md)), so back-office pages get their header/footer automatically.

### 4. Super-admin-only content

```php
if (Helper::Admin()->isSuper()) {
    // menus/buttons only a super admin may see
}
```

`isSuper()` asks your [`AdminServiceInterface::isSuper($admin_id)`](../reference/GlobalAdmin-AdminServiceInterface.md) every time; the framework does not cache it, and buttons/menus are decided by its return value.

### 5. The back-office menu: `Ext\PermissionMenu`

[`PermissionMenu`](../reference/Ext-PermissionMenu.md) (an `Ext\*` extension, so mount it in `ext` to use it) uses [`RouteLister`](../reference/Ext-RouteLister.md) to scan the routes of **back-office controllers** (classes implementing [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md); extending `AdminControllerBase` already satisfies that) and builds the menu/permission tree:

| Mode | How |
|---|---|
| **Comment mode** | Put annotations such as `@menu_directory`, `@menu`, `@menu_action`, `@menu_permission` on the controller class/methods (an example is in `tests/data_for_tests/Ext/PermissionMenu/Controller/AdminController.php`) |
| **Metadata mode** | The controller implements [`PermissionMenuMetaInterface`](../reference/Ext-PermissionMenuMetaInterface.md) and `__permissionMenuMeta()` returns the whole table |
| **Persisted mode** | `buildAndSaveToConfigJsonFile()` writes the tree into config and `loadAdminPermissionMenu()` reads it back at runtime, avoiding a route scan on every request |

```php
// run once at deploy time or from a scheduled task (or from CLI, see Chapter 2-16)
PermissionMenu::_()->buildAndSaveToConfigJsonFile();
// read it back at runtime
$tree = PermissionMenu::_()->loadAdminPermissionMenu();
```

The menu file path comes from the hidden option `permission_menu_tree_for_admin`; `loadAll()` merges the root app's and every child app's menus into one tree (phase-safe). The assembly, the metadata contract and "troubleshooting with `RouteLister::_()->command_routes()`" are in [Chapter 4-13](ext-classes.md) §6.

## Common patterns

**① Check permission before each back-office action, log after it**

```php
public function delete()
{
    $id = (int)Helper::GET('id');
    if (!Helper::Admin()->canAccess()) {        // no arguments: it takes the current route context
        return;                                  // the redirect/error is left to the base class's onNeedPermission()
    }
    MyService::_()->delete($id);
    Helper::Admin()->log("deleted #{$id}", 'delete', ['id' => $id]);
    Helper::Show302('admin/list');
}
```

**② Record data ownership with the admin id**

```php
NoteModel::_()->insert(['admin_id' => Helper::AdminId(), 'body' => $body]);
```

**③ Use the login information in a back-office view**

```php
<?php if (!empty($__logined_id)): ?>
    <?= __h($__logined_name) ?> · <a href="<?= __h($__logined_url_logout) ?>">log out</a>
<?php endif; ?>
```

**④ Add one menu entry (comment mode)**

```php
/**
 * @menu_directory System settings
 * @menu_icon fa fa-folder
 * @menu_weight 10
 */
class ConfigController extends AdminControllerBase
{
    /**
     * @menu Site configuration
     * @menu_icon cog
     */
    public function action_index()
    {
    }
    /**
     * @menu_action Save
     * @menu_permission #save Save the site configuration
     */
    public function action_save()
    {
        // the permission points declared with @menu_permission appear in the menu tree, ready for role assignment
    }
}
```

> `@menu_permission` is written as `#method-name permission-name` (relative to the current controller) or `/absolute/path permission-name`; one method may carry several. The complete rules are in the [reference manual](../reference/Ext-PermissionMenu.md).

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| Calling `Helper::AdminId()` sends the page straight into a 302 | `$check_login` defaults to `true`, so "not logged in" redirects to the login page and ends the request | Pass `false` if you want to decide yourself (§2) |
| Writing `Helper::AdminId()` in the business layer reports a missing method | The business-layer Helper only has `AdminService()` | Pass `$adminId` into the business method from the controller |
| `DuckPhpSystemException: No GlobalAdmin Provider.` | The admin system is not wired yet (`ext` is not mounted) | See [Chapter 4-12](impl-admin.md) |
| `canAccess()` with no arguments reports an empty route context | It is called outside a routed action (CLI / child phase) | Pass `canAccess($url, $class, $method)` explicitly (note that `$url` comes first) |
| Back-office pages have no header/footer | The controller does not extend `AdminControllerBase`, or the two view-data keys are not set to true | Extend it, or call `assignViewData` yourself ([Chapter 2-19 §6](user.md)) |
| The back-office menu is empty | The controller does not implement `AdminControllerInterface`, or no `@menu*` annotations are written | Extend `AdminControllerBase`; or write the annotations / use `__permissionMenuMeta()` |
| An unauthorised request still gets the default 302/JSON | `onNeedPermission()` was overridden in the wrong place | Override it in **your own controller base class** (§3) |

## Next steps

- [Chapter 2-19 Using the user system](user.md): the front-end entry points.
- [Chapter 4-12 Implementing the admin system](impl-admin.md): who provides this project's admin system, and how the options are configured.
- [Chapter 2-13 The event system](events.md): how to listen for `EVENT_ACTION_ADMIN_*` (around login/logout).
- [Chapter 2-16 The command line and scheduled tasks](cli.md): persisting the menu and inspecting the route table from the CLI.
- [Chapter 3-5 Overriding and replacement](overriding.md): swapping the back-office view header/footer.
- Reference manual: [Admin](../reference/GlobalAdmin-Admin.md), [AdminActionInterface](../reference/GlobalAdmin-AdminActionInterface.md), [AdminLoginActionInterface](../reference/GlobalAdmin-AdminLoginActionInterface.md), [GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md), [AdminControllerInterface](../reference/GlobalAdmin-AdminControllerInterface.md), [AdminServiceInterface](../reference/GlobalAdmin-AdminServiceInterface.md), [Ext\PermissionMenu](../reference/Ext-PermissionMenu.md), [Ext\RouteLister](../reference/Ext-RouteLister.md)
