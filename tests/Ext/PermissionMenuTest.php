<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\PermissionMenu;
use DuckPhp\Core\App;
use DuckPhp\Core\AutoLoader;
use DuckPhp\Core\Route;
use tests_Ext_PermissionMenu\Controller\AdminController;
use tests_Ext_PermissionMenu\System\ChildMenuApp;
use tests_Ext_PermissionMenu\System\PermissionMenuApp;

class PermissionMenuTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(PermissionMenu::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(PermissionMenu::class);

        AutoLoader::_()->init([
            'path' => $path,
            'namespace' => 'tests_Ext_PermissionMenu',
            'path_namespace' => '',
        ])->run();

        PermissionMenuApp::_()->init(['path' => $path]);

        $menu = PermissionMenu::_();
        $my = MyPermissionMenu::_();

        ////////////////////////////////////////////////////////////////////
        // 1) annotation parsers
        ////////////////////////////////////////////////////////////////////
        $this->assertSame(0, $my->parseWeightPub(''));
        $this->assertSame(7, $my->parseWeightPub('@menu_weight 7'));
        $this->assertSame(-3, $my->parseWeightPub('@menu_weight -3'));

        $this->assertNull($my->parseAnnotatedLinePub('', 'menu'));
        $this->assertNull($my->parseAnnotatedLinePub("/**\n * @menu_icon\n */", 'menu_icon'));
        $this->assertSame('Dashboard', $my->parseAnnotatedLinePub("/**\n * @menu Dashboard\n */", 'menu'));
        // the value runs to the end of the line: blanks are kept, the value is trimmed
        $this->assertSame('Sub Menu Here', $my->parseAnnotatedLinePub("/**\n * @menu Sub Menu Here   \n */", 'menu'));
        // '@menu_directory' must not swallow '@menu_directory_url'
        $dir_doc = "/**\n * @menu_directory Admin\\System\n * @menu_directory_url admin/system\n */";
        $this->assertSame('Admin\\System', $my->parseAnnotatedLinePub($dir_doc, 'menu_directory'));
        $this->assertSame('admin/system', $my->parseAnnotatedLinePub($dir_doc, 'menu_directory_url'));

        $this->assertSame([], $my->parseMultiAnnotatedLinePub('', 'menu_permission'));
        $multi = "/**\n * @menu_permission #a Alpha One\n * @menu_permission b Beta\n */";
        $this->assertSame(
            [['#a', 'Alpha One'], ['b', 'Beta']],
            $my->parseMultiAnnotatedLinePub($multi, 'menu_permission')
        );

        // docblock readers: unknown class / unknown method are tolerated
        $this->assertSame('', $my->getClassDocPub('No\\Such\\ClassController'));
        $this->assertSame('', $my->getMethodDocPub('No\\Such\\ClassController', 'action_x'));
        $this->assertSame('', $my->getMethodDocPub(AdminController::class, 'no_such_method'));
        $this->assertStringContainsString('@menu_directory', $my->getClassDocPub(AdminController::class));

        $this->assertNull($my->getClassMenuMetaPub('No\\Such\\ClassController'));
        $this->assertNull($my->getClassMenuMetaPub(AdminController::class));

        // the menu config option is not set yet
        $this->assertNull($my->getMenuJsonFileConfigPub());

        ////////////////////////////////////////////////////////////////////
        // 2) build() on routes pointing at unknown controllers
        ////////////////////////////////////////////////////////////////////
        $unknown = $menu->build([
            ['controller' => '', 'method' => 'index', 'url' => ''],
            ['controller' => 'No\\Such\\ClassController', 'method' => 'action_x', 'url' => 'x'],
        ]);
        // both controllers end up in the same fallback directory (no @menu_directory)
        $this->assertCount(1, $unknown);
        $this->assertSame('NoName', $unknown[0]['name']);
        $this->assertSame('', $unknown[0]['url']);
        $this->assertSame(0, $unknown[0]['type']);
        $child_names = array_column($unknown[0]['children'], 'name');
        sort($child_names);
        $this->assertSame(['action_x', 'index'], $child_names);

        ////////////////////////////////////////////////////////////////////
        // 3) build() from the real routes of the test app
        ////////////////////////////////////////////////////////////////////
        $routes = $my->getRoutesPub(false);
        $this->assertNotEmpty($routes);
        $this->assertArrayHasKey('url', $routes[0]);
        $this->assertSame(AdminController::class, $this->controllerOf($routes, 'Admin/index'));

        $tree = $menu->build($routes);
        // __permissionMenuMeta() controllers put their items at the top level,
        // the others create a directory per @menu_directory (Admin\System), and
        // controllers without class annotations fall back to the NoName directory.
        $this->assertSame(['Admin', 'Handbook', 'Meta Action', 'Meta Menu', 'NoName', 'Users'], $this->topNames($tree));
        // same level sort by @menu_weight (desc), weights are stripped on output
        $this->assertSame('Handbook', $tree[0]['name']);
        // @menu_directory_url gives the directory its own url
        $handbook = $this->findNode($tree, 'Handbook');
        $this->assertSame('handbook/index', $handbook['url']);
        $this->assertSame('book', $handbook['icon']);
        $this->assertSame('Handbook Home', $handbook['children'][0]['name']);

        $admin = $this->findNode($tree, 'Admin');
        // 'Admin\System' is split into two levels, the intermediate level has no url
        $this->assertNull($admin['url']);
        $this->assertSame(0, $admin['type']);
        // AdminController and UserController both declare @menu_directory Admin\System,
        // so their menus are merged into ONE 'System' directory (leaf name = last part)
        $this->assertCount(1, $admin['children']);
        $system = $admin['children'][0];
        $this->assertSame('System', $system['name']);
        // the derived url (first method's dirname + '/#') is used when no url annotation
        $this->assertSame('Admin/#', $system['url']);
        // annotation values run to the end of the line, so an icon may contain blanks
        $this->assertSame('fa fa-folder', $system['icon']);

        // multi word names are kept as a whole, unannotated methods keep the action_ prefix
        $system_names = array_column($system['children'], 'name');
        sort($system_names);
        $this->assertSame(
            ['Abs Place Here', 'Dashboard Panel', 'Edit User', 'Profile', 'action_bare_icon', 'action_edit', 'action_orders', 'action_plain'],
            $system_names
        );
        $dashboard = $this->findNode($system['children'], 'Dashboard Panel');
        $this->assertSame(1, $dashboard['type']);
        $this->assertSame('Admin/index', $dashboard['url']);
        $this->assertSame('home', $dashboard['icon']);
        $this->assertArrayNotHasKey('weight', $dashboard);
        // @menu_weight 5 (Dashboard Panel) and 3 (Profile) sort before the zero weight ones
        $this->assertSame('Dashboard Panel', $system['children'][0]['name']);
        $this->assertSame('Profile', $system['children'][1]['name']);

        $plain = $this->findNode($system['children'], 'action_plain');
        $this->assertSame(2, $plain['type']);
        $this->assertSame('Admin/plain', $plain['url']);
        // a bare "@menu_icon" without a value is ignored, the key stays null
        $this->assertNull($this->findNode($system['children'], 'action_bare_icon')['icon']);
        // "#url Name" permissions are prefixed with the method url,
        // a permission without a name ("#noname") is dropped
        $permission = $this->findNode($system['children'], 'Edit User');
        $this->assertSame(3, $permission['type']);
        $this->assertSame('Admin/edit#edit', $permission['url']);
        $this->assertNull($permission['icon']);
        $this->assertSame('/abs/place', $this->findNode($system['children'], 'Abs Place Here')['url']);

        // a method level @menu_directory creates its own top level directory
        $users = $this->findNode($tree, 'Users');
        // this directory node has no url annotation of its own
        $this->assertNull($users['url']);
        $users_names = array_column($users['children'], 'name');
        sort($users_names);
        $this->assertSame(['Manage', 'Orders List'], $users_names);
        $manage = $this->findNode($users['children'], 'Manage');
        // ...but the @menu_directory_url of the method that created it is used
        $this->assertSame('users/manage', $manage['url']);
        $this->assertSame('User List', $manage['children'][0]['name']);

        // __permissionMenuMeta() of MetaController is used as is (multi word names kept)
        $meta_menu = $this->findNode($tree, 'Meta Menu');
        $this->assertSame(1, $meta_menu['type']);
        $this->assertSame('Meta/index', $meta_menu['url']);
        $this->assertNull($this->findNode($tree, 'Meta Action')['url']);

        // BadMetaController throws and NullMetaController returns null: both fall back
        // to annotation mode, in the NoName directory
        $no_name = $this->findNode($tree, 'NoName');
        $this->assertSame('BadMeta/#', $no_name['url']);
        $no_name_children = array_column($no_name['children'], 'name');
        sort($no_name_children);
        $this->assertSame(['Bad Meta Item', 'Null Meta Item'], $no_name_children);

        ////////////////////////////////////////////////////////////////////
        // 4) resolveUrls / walkTree
        ////////////////////////////////////////////////////////////////////
        $walked = [
            ['name' => 'A', 'url' => 'a', 'children' => [['name' => 'B', 'url' => 'b']]],
        ];
        $depths = [];
        $menu->walkTree($walked, function (&$node, int $depth) use (&$depths) {
            $depths[] = $node['name'] . ':' . $depth;
        });
        $this->assertSame(['A:0', 'B:1'], $depths);

        $menu->resolveUrls($walked, '/admin/');
        $this->assertSame('/admin/a', $walked[0]['url']);
        $this->assertSame('/admin/b', $walked[0]['children'][0]['url']);

        ////////////////////////////////////////////////////////////////////
        // 5) loadAdminPermissionMenu()
        ////////////////////////////////////////////////////////////////////
        // force_build ignores the config file
        $this->assertSame($this->topNames($tree), $this->topNames($menu->loadAdminPermissionMenu(true)));

        // no config file -> built from routes
        $this->assertSame($this->topNames($tree), $this->topNames($menu->loadAdminPermissionMenu(false)));

        // json config file: urls are completed with the mount prefix
        PermissionMenuApp::_()->options['permission_menu_tree_for_admin'] = 'menu.json';
        $tree_json = $menu->loadAdminPermissionMenu(false);
        $this->assertSame('Dashboard', $tree_json[0]['name']);
        $this->assertSame('/Dash/index', $tree_json[0]['url']);
        $this->assertSame('/Dash/sub', $tree_json[0]['children'][0]['url']);

        // php config file returning an array
        PermissionMenuApp::_()->options['permission_menu_tree_for_admin'] = 'menu_array.config.php';
        $tree_php = $menu->loadAdminPermissionMenu(false);
        $this->assertSame('From Php', $tree_php[0]['name']);
        $this->assertSame('/php/index', $tree_php[0]['url']);

        // php config file that does not return an array -> empty
        PermissionMenuApp::_()->options['permission_menu_tree_for_admin'] = 'menu_scalar.config.php';
        $this->assertSame([], $menu->loadAdminPermissionMenu(false));

        // config file that does not exist at all -> empty
        PermissionMenuApp::_()->options['permission_menu_tree_for_admin'] = 'menu_missing.config.php';
        $this->assertSame([], $menu->loadAdminPermissionMenu(false));

        ////////////////////////////////////////////////////////////////////
        // 6) buildAndSaveToConfigJsonFile()
        ////////////////////////////////////////////////////////////////////
        $built_file = $path . 'config/menu_built.json';
        PermissionMenuApp::_()->options['permission_menu_tree_for_admin'] = 'menu_built.json';
        // with a mount prefix the saved urls are relative to the app prefix
        Route::_()->options['controller_url_prefix'] = 'admin';
        PermissionMenuApp::_()->options['controller_url_prefix'] = 'admin';

        $menu->buildAndSaveToConfigJsonFile();

        $this->assertFileExists($built_file);
        $saved = json_decode((string) file_get_contents($built_file), true);
        $saved_urls = $this->collectUrls($saved);
        $this->assertContains('Admin/index', $saved_urls);
        $this->assertNotContains('adminAdmin/index', $saved_urls);
        unlink($built_file);

        // without the option nothing is written at all
        PermissionMenuApp::_()->options['permission_menu_tree_for_admin'] = null;
        $menu->buildAndSaveToConfigJsonFile();
        $this->assertFileDoesNotExist($built_file);

        Route::_()->options['controller_url_prefix'] = '';
        PermissionMenuApp::_()->options['controller_url_prefix'] = '';

        ////////////////////////////////////////////////////////////////////
        // 7) loadAll() / mergeAppsMenus()
        ////////////////////////////////////////////////////////////////////
        // an app entry that is not a real child app is skipped
        PermissionMenuApp::_()->options['app']['NoSuchApp'] = false;

        $root_phase = App::Phase();
        $tree_all = $menu->loadAll(false);
        $this->assertSame($root_phase, App::Phase());

        $all_names = array_column($tree_all, 'name');
        // the root menu is loaded exactly once (loadAdminPermissionMenu), even though
        // the walk starts from the root phase itself
        $this->assertSame(1, count(array_keys($all_names, 'Admin')));
        $this->assertSame(1, count(array_keys($all_names, 'Child Menu')));
        $this->assertSame(1, count(array_keys($all_names, 'Grand Menu')));
        $this->assertSame(1, count(array_keys($all_names, 'Leaf Menu')));
        $all_urls = $this->collectUrls($tree_all);
        $this->assertContains('Admin/index', $all_urls);
        $this->assertContains('/child/index', $all_urls);
        $this->assertContains('/grand/index', $all_urls);
        $this->assertContains('/leaf/index', $all_urls);

        // calling from a child phase: that child's own menu is not loaded twice,
        // but its child apps are still merged in
        $child = PermissionMenuApp::_()->toThisChild(ChildMenuApp::class);
        $this->assertNotNull($child);
        $child_phase = App::Phase();
        $this->assertNotSame($root_phase, $child_phase);

        $tree_in_child = $menu->loadAll(false);
        $this->assertSame($child_phase, App::Phase());
        $child_names = array_column($tree_in_child, 'name');
        // 'Child Menu' stays once (loaded as the current phase) and is NOT merged again
        $this->assertSame(1, count(array_keys($child_names, 'Child Menu')));
        $this->assertSame(1, count(array_keys($child_names, 'Admin')));
        $this->assertContains('Grand Menu', $child_names);
        // the menus below the ignored phase are still merged
        $this->assertSame(1, count(array_keys($child_names, 'Leaf Menu')));
        $this->assertContains('/leaf/index', $this->collectUrls($tree_in_child));

        App::Root(true);
        $this->assertSame($root_phase, App::Phase());

        ////////////////////////////////////////////////////////////////////
        // 8) permissionMenuTreeToSideMenuTree()
        ////////////////////////////////////////////////////////////////////
        $side = $menu->permissionMenuTreeToSideMenuTree([
            ['name' => 'Dir', 'url' => '/dir', 'icon' => 'icon-dir', 'type' => 0, 'children' => [
                ['name' => 'Menu', 'url' => '/menu', 'icon' => null, 'type' => 1],
                ['name' => 'Action', 'url' => '/action', 'type' => 2],
            ]],
            ['name' => 'Top Menu', 'url' => '/top', 'icon' => null, 'type' => 1],
            ['name' => 'Empty Dir', 'url' => '', 'type' => 0, 'children' => []],
            ['name' => 'No Type', 'url' => ''],
            ['name' => 'All Children Dropped', 'url' => '', 'type' => 0, 'children' => [
                ['name' => 'x', 'url' => 'x', 'type' => 3],
            ]],
            ['name' => 'Perm', 'url' => '/perm', 'type' => 3],
        ]);
        $this->assertSame(['Dir', 'Top Menu'], array_column($side, 'name'));
        $this->assertSame('icon-dir', $side[0]['icon']);
        $this->assertSame(['Menu'], array_column($side[0]['children'], 'name'));
        $this->assertArrayNotHasKey('children', $side[1]);

        ////////////////////////////////////////////////////////////////////
        // 9) recordsetToTree() / treeToRecordset()
        ////////////////////////////////////////////////////////////////////
        $tree_from_recordset = $menu->recordsetToTree([
            ['id' => 1, 'pid' => 0, 'title' => 'Root A'],
            ['id' => 2, 'pid' => 1, 'title' => 'Child A1'],
            ['id' => 3, 'pid' => 99, 'title' => 'Orphan'],
            ['title' => 'No Id'],
        ]);
        $this->assertCount(2, $tree_from_recordset);
        $this->assertSame(1, $tree_from_recordset[0]['id']);
        $this->assertSame(2, $tree_from_recordset[0]['children'][0]['id']);
        $this->assertSame(3, $tree_from_recordset[1]['id']);

        $custom = $menu->recordsetToTree([
            ['node_id' => 10, 'parent' => 0, 'name' => 'A'],
            ['node_id' => 11, 'parent' => 10, 'name' => 'B'],
        ], 'node_id', 'parent', 0);
        $this->assertSame(11, $custom[0]['children'][0]['node_id']);
        $this->assertSame(10, $custom[0]['children'][0]['parent']);

        $recordset = $menu->treeToRecordset([
            ['id' => 1, 'name' => 'A', 'children' => [
                ['id' => 2, 'name' => 'B'],
            ]],
            ['name' => 'No Id'],
        ]);
        $this->assertCount(3, $recordset);
        $this->assertSame(0, $recordset[0]['pid']);
        $this->assertSame(1, $recordset[1]['pid']);
        $this->assertSame('No Id', $recordset[2]['name']);
        $this->assertArrayNotHasKey('children', $recordset[0]);

        \LibCoverage\LibCoverage::End();
    }

    private function findNode(array $nodes, string $name): array
    {
        foreach ($nodes as $node) {
            if (($node['name'] ?? null) === $name) {
                return $node;
            }
        }
        $this->fail("menu node [$name] not found");
        return [];
    }

    private function topNames(array $nodes): array
    {
        $names = array_column($nodes, 'name');
        sort($names);
        return $names;
    }

    private function collectUrls(array $nodes, array &$urls = []): array
    {
        foreach ($nodes as $node) {
            $urls[] = (string) ($node['url'] ?? '');
            if (!empty($node['children'])) {
                $this->collectUrls($node['children'], $urls);
            }
        }
        return $urls;
    }

    private function controllerOf(array $routes, string $url): string
    {
        foreach ($routes as $route) {
            if ($route['url'] === $url) {
                return $route['controller'];
            }
        }
        return '';
    }
}

class MyPermissionMenu extends PermissionMenu
{
    public function getClassDocPub(string $class): string
    {
        return $this->getClassDoc($class);
    }
    public function getMethodDocPub(string $class, string $method): string
    {
        return $this->getMethodDoc($class, $method);
    }
    public function getClassMenuMetaPub(string $controller): ?array
    {
        return $this->getClassMenuMeta($controller);
    }
    public function getMenuJsonFileConfigPub()
    {
        return $this->getMenuJsonFileConfig();
    }
    public function getRoutesPub(bool $trim_url)
    {
        return $this->getRoutes($trim_url);
    }
    public function parseAnnotatedLinePub(string $doc, string $tag): ?string
    {
        return $this->parseAnnotatedLine($doc, $tag);
    }
    public function parseMultiAnnotatedLinePub(string $doc, string $tag): array
    {
        return $this->parseMultiAnnotatedLine($doc, $tag);
    }
    public function parseWeightPub(string $doc): int
    {
        return $this->parseWeight($doc);
    }
}
