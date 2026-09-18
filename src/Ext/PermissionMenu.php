<?php declare(strict_types=1);
/**
 * DuckPhp Admin System - Admin Tree Builder
 */
namespace DuckPhp\Ext;

use DuckPhp\Component\RouteLister;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class PermissionMenu extends ComponentBase
{
    public function buildAndSaveToConfigJsonFile()
    {
        $routes = $this->getRoutes(true);
        $tree = $this->build($routes);
        
        $menu_file = $this->getMenuJsonFileConfig();
        if (!$menu_file) {
            return;
        }
        $filename = App::_()->getConfigFile($menu_file);
        $data = json_encode($tree, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $data);
    }
    public function loadAdminPermissionMenu(bool $force_build = false)
    {

        if ($force_build) {
            $routes = $this->getRoutes(false);
            $menuTree = $this->build($routes);
            return $menuTree;
        }
        $menu_file = $this->getMenuJsonFileConfig();
        if ($menu_file) {
            $filename = App::_()->getConfigFile($menu_file);
            if ('.json' === substr($filename, -strlen('.json'))) {
                $menuTree = json_decode(file_get_contents($filename), true);
            } else {
                $menuTree = @include $filename;
                $menuTree = is_array($menuTree) ? $menuTree : [];
            }
            $prefix = App::_()->options['controller_url_prefix'] ?? '';
            $prefix = '/' . $prefix;
            $menuTree = $this->resolveUrls($menuTree, $prefix);
        } else {
            $routes = $this->getRoutes(false);
            $menuTree = $this->build($routes);
        }

        return $menuTree;
    }
    protected function getMenuJsonFileConfig()
    {
        return  App::_()->options['permission_menu_tree_for_admin'] ?? null;
    }
    /**
     * Load the permission menu of the whole app tree.
     *
     * The menu of the app of the current phase is loaded first, then the root app and
     * every child app menu are merged in (without loading the current app's menu twice).
     *
     * @param bool $force_build true: always build from routes, ignore the menu config file
     * @return array
     */
    public function loadAll(bool $force_build = false)
    {
        $current_phase = App::Phase();
        $tree = $this->loadAdminPermissionMenu($force_build);

        App::Root(true);
        $this->mergeAppsMenus($tree, $current_phase, $force_build);
        App::Phase($current_phase);

        return $tree;
    }
    /**
     * Recursively merge the app tree's menus (starting from root)
     *
     * The menu of $ignore_phase is skipped here because loadAll() already loaded it,
     * but its child apps are still merged (they were not loaded yet).
     *
     * @param array &$tree Tree to merge into
     * @param string $ignore_phase Phase to ignore (the phase loadAll() started from)
     */
    protected function mergeAppsMenus(array &$tree, string $ignore_phase, bool $force_build): void
    {
        $app = App::_();
        $current_phase = App::Phase();
        $child_apps = $app->options['app'] ?? [];

        if ($current_phase !== $ignore_phase) {
            $item = $this->loadAdminPermissionMenu($force_build);
            $tree = array_merge($tree, $item);
        }
        foreach ($child_apps as $class => $app_options) {
            $child_app = $app->toThisChild($class);
            if ($child_app === null) {
                continue;
            }
            $this->mergeAppsMenus($tree, $ignore_phase, $force_build);
            App::Phase($current_phase);
        }
    }
    protected function getRoutes(bool $trim_url = false)
    {
        $routes = RouteLister::_()->listAll(false, true, true);
        if (!$trim_url) {
            return $routes;
        }
        // Convert to relative path
        $prefix = App::_()->options['controller_url_prefix'] ?? '';
        foreach ($routes as &$route) {
            $route['url'] = substr($route['url'], strlen($prefix));
        }
        unset($route);
        return $routes;
    }
    ////////////////////////////////////////////////////////
    /**
     * Build menu tree: RouteLister scans routes, generates Group -> Directory -> Menu/Action tree via annotations
     *
     * Supports two modes:
     * 1. Annotation mode: Write annotations directly on controller classes and methods
     * 2. __permissionMenuMeta mode: If class provides public static function __permissionMenuMeta() returning menu metadata
     *
     * Class-level annotations:
     * - @menu_directory Name     Top-level group, supports \ split for multi-level directories.
     *                           The name runs to the end of the line (blanks allowed, trimmed)
     * - @menu_directory_url Url  Optional: url of that directory node;
     *                           defaults to the first method's dirname + '/#', e.g. Admin/index -> Admin/#
     * - @menu_icon IconName        Directory icon (blanks allowed, trimmed)
     * - @menu_weight N           Layer weight, larger = higher priority
     *
     * Method-level annotations:
     * - @menu_directory Name Optional Top-level group, supports \ split for multi-level directories, inserts into corresponding directory
     * - @menu_directory_url Url Optional Url of that directory node
     * - @menu_icon IconName     Sets icon for menu/action node
     * - @menu Name               Menu (type=1), url takes full route path
     * - @menu_action Name        Action (type=2)
     * - @menu_permission #url Name   Special (type=3), #url is prefixed with method url
     * - @menu_weight N           Layer weight
     * - Public method with no annotation   Treated as Action (type=2), name is method name
     *
     * Every annotation value runs to the end of the line and is trimmed, so names/icons
     * may contain blanks ("@menu_action User List" is named "User List").
     * Directory urls given by @menu_directory_url are completed by resolveUrls() later.
     *
     * Post-processing: split sub-levels, sort
     *
     * @return array[] Simplified menu tree structure
     */
    public function build(array $routes): array
    {
        // 1. Group by controller
        $controllers = [];
        foreach ($routes as $route) {
            $controller = (string) ($route['controller'] ?? '');
            $method = (string) ($route['method'] ?? '');
            $url = (string) ($route['url'] ?? '');
            $controllers[$controller][$method] = $url;
        }

        // 2. Process each controller
        $items = [];
        foreach ($controllers as $controller => $methods) {
            // Check __permissionMenuMeta static method
            $meta = $this->getClassMenuMeta($controller);
            if ($meta !== null) {
                $items = array_merge($items, $this->processMenuMetaForController($meta, $methods));
                continue;
            }

            // Annotation mode
            $classDoc = $this->getClassDoc($controller);
            // Class-level directory, url, icon, weight annotations
            $dirAnno = $this->parseAnnotatedLine($classDoc, 'menu_directory');
            $dirUrlAnno = $this->parseAnnotatedLine($classDoc, 'menu_directory_url');
            $dirIcon = $this->parseAnnotatedLine($classDoc, 'menu_icon');
            $dirWeight = $this->parseWeight($classDoc);

            // Process all methods under this controller, collect child nodes
            $childItems = [];
            foreach ($methods as $method => $url) {
                $childItems = array_merge($childItems, $this->collectMethodItems($controller, $method, $url));
            }

            // Calculate directory url: first method's dirname + '/#'
            $dirUrl = '';
            if (!empty($methods)) {
                $firstUrl = reset($methods);
                $pos = strrpos($firstUrl, '/');
                if ($pos !== false) {
                    $dirUrl = substr($firstUrl, 0, $pos + 1) . '#';
                }
            }
            // @menu_directory_url wins over the url derived from the first method
            if ($dirUrlAnno !== null) {
                $dirUrl = $dirUrlAnno;
            }

            // Generate directory node, attach children under it
            if (!empty($childItems)) {
                $items[] = [
                    'name' => $dirAnno ?? 'NoName',
                    'url' => $dirUrl,
                    'icon' => $dirIcon,
                    'type' => 0,
                    'weight' => $dirWeight,
                    'children' => $childItems,
                ];
            }
        }

        // 3. Split sub-levels (handle \ split for multi-level directories)
        $tree = $this->splitSubLevels($items);

        // 4. Sort (same-level sort, weight is only effective within same level)
        $this->sortTree($tree);
        return $tree;
    }

    /**
     * Get class's __permissionMenuMeta method returning menu metadata
     *
     * @param string $controller Controller class name
     * @return array|null Returns array if exists, otherwise null
     */
    protected function getClassMenuMeta(string $controller): ?array
    {
        if (!class_exists($controller)) {
            return null;
        }
        if (!method_exists($controller, '__permissionMenuMeta')) {
            return null;
        }
        try {
            $refClass = new \ReflectionClass($controller);
            $instance = $refClass->newInstanceWithoutConstructor();
            return $instance->__permissionMenuMeta() ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Process __permissionMenuMeta return data (by all methods under controller)
     *
     * @param array $meta __permissionMenuMeta return array
     * @param array $methods All methods under controller [method => url]
     * @return array items array
     */
    protected function processMenuMetaForController(array $meta, array $methods): array
    {
        $items = [];
        foreach ($meta as $item) {
            $name = (string) ($item['name'] ?? '');
            $type = (int) ($item['type'] ?? 1);
            $url = ($item['url'] ?? null);
            $weight = (int) ($item['weight'] ?? 0);

            $items[] = [
                'name' => $name,
                'url' => $url,
                'type' => $type,
                'weight' => $weight,
            ];
        }
        return $items;
    }

    /**
     * Collect method annotation info, generate child nodes
     *
     * @param string $controller Controller class name
     * @param string $method Method name
     * @param string $url Route path
     * @return array Child node array
     */
    protected function collectMethodItems(string $controller, string $method, string $url): array
    {
        $items = [];
        $mDoc = $this->getMethodDoc($controller, $method);
        $weight = $this->parseWeight($mDoc);

        // Method's @menu_directory / @menu_directory_url annotations (recorded for splitSubLevels)
        $methodDir = $this->parseAnnotatedLine($mDoc, 'menu_directory');
        $methodDirUrl = $this->parseAnnotatedLine($mDoc, 'menu_directory_url');
        // Method's @menu_icon annotation
        $methodIcon = $this->parseAnnotatedLine($mDoc, 'menu_icon');

        // @menu first, then @menu_action, default action
        $menuAnno = $this->parseAnnotatedLine($mDoc, 'menu');
        $actionAnno = $this->parseAnnotatedLine($mDoc, 'menu_action');
        if ($menuAnno !== null) {
            $name = $menuAnno;
            $type = 1;
        } elseif ($actionAnno !== null) {
            $name = $actionAnno;
            $type = 2;
        } else {
            $name = $method;
            $type = 2;
            $weight = 0;
        }
        $items[] = [
            'name' => $name,
            'url' => $url,
            'type' => $type,
            'weight' => $weight,
            'directory' => $methodDir ?? '',
            'directory_url' => $methodDirUrl,
            'icon' => $methodIcon,
        ];

        // @menu_permission #url Name (may have multiple, put at end)
        foreach ($this->parseMultiAnnotatedLine($mDoc, 'menu_permission') as $permAnno) {
            $permUrl = $permAnno[0] ?? '';
            $permName = $permAnno[1] ?? '';
            if ($permName === '') {
                continue;
            }
            if (strpos($permUrl, '#') === 0) {
                $permUrl = $url . $permUrl;
            }
            $items[] = [
                'name' => $permName,
                'url' => $permUrl,
                'type' => 3,
                'weight' => $weight,
                'directory' => $methodDir ?? '',
                'directory_url' => $methodDirUrl,
                'icon' => null,
            ];
        }

        return $items;
    }

    /**
     * Parse multi-line same-type annotations (e.g. multiple @menu_permission)
     *
     * Each line is split into "first token" and "the rest of the line, trimmed",
     * so the second value may contain blanks ("@menu_permission #edit Edit User").
     *
     * @param string $doc docblock
     * @param string $tag annotation name
     * @return array<int,array{0:string,1:string}> 2D array, one parse result per line
     */
    protected function parseMultiAnnotatedLine(string $doc, string $tag): array
    {
        $results = [];
        if (!preg_match_all('/@' . $tag . '\s+([^*\n]+)/', $doc, $matches)) {
            return $results;
        }
        foreach ($matches[1] as $match) {
            $parts = preg_split('/\s+/', trim($match), 2);
            $first = (string) ($parts[0] ?? '');
            $second = trim((string) ($parts[1] ?? ''));
            $results[] = [$first, $second];
        }
        return $results;
    }

    /**
     * Split sub-levels (handle \ split for multi-level directories, and children's directory info)
     *
     * @param array $nodes Raw tree nodes
     * @return array Processed tree
     */
    protected function splitSubLevels(array $nodes): array
    {
        $tree = [];

        foreach ($nodes as $node) {
            // Clean children's temporary fields, separate children with directory
            $normalChildren = [];
            $dirChildren = [];
            $dirUrls = [];
            foreach ($node['children'] ?? [] as $child) {
                $dir = $child['directory'] ?? '';
                $dirUrl = $child['directory_url'] ?? null;
                // 'directory' and 'directory_url' are temporary fields, the rest is output
                unset($child['directory'], $child['directory_url']);
                if ($dir !== '') {
                    $dirChildren[$dir][] = $child;
                    if ($dirUrl !== null && !isset($dirUrls[$dir])) {
                        // first @menu_directory_url of that directory wins
                        $dirUrls[$dir] = $dirUrl;
                    }
                } else {
                    $normalChildren[] = $child;
                }
            }
            $node['children'] = $normalChildren;

            // Handle node name's \ split
            $parts = explode('\\', $node['name']);
            $this->mergeNode($tree, $parts, $node);

            // Handle children with directory
            foreach ($dirChildren as $dirName => $children) {
                $dirParts = explode('\\', $dirName);
                $dirNode = [
                    'name' => $dirParts[count($dirParts) - 1],
                    'url' => $dirUrls[$dirName] ?? null,
                    'type' => 0,
                    'children' => $children,
                ];
                $this->mergeNode($tree, $dirParts, $dirNode);
            }
        }

        return $tree;
    }

    /**
     * Merge node into tree (create directories by path)
     *
     * @param array &$tree Target tree (by reference)
     * @param array $parts Path split array
     * @param array $node Node to merge
     */
    protected function mergeNode(array &$tree, array $parts, array $node): void
    {
        $name = array_shift($parts);
        $isLast = empty($parts);

        // Find same-name directory node
        foreach ($tree as &$item) {
            if ($item['name'] === $name && $item['type'] === 0) {
                if ($isLast) {
                    $item['children'] = array_merge($item['children'], $node['children'] ?? []);
                } else {
                    $this->mergeNode($item['children'], $parts, $node);
                }
                return;
            }
        }
        unset($item);

        // Not found, create new node
        if ($isLast) {
            // $node may carry the full "A\B" path as its name; the leaf is only $name
            $node['name'] = $name;
            $tree[] = $node;
        } else {
            $tree[] = [
                'name' => $name,
                'url' => null,
                'type' => 0,
                'children' => [],
            ];
            end($tree);
            $idx = key($tree);
            $this->mergeNode($tree[$idx]['children'], $parts, $node);
        }
    }
    /**
     * Recursively sort tree: same-level sort by weight, larger weight comes first, clean weight field on output
     *
     * @param array &$nodes Tree node array (by reference)
     */
    protected function sortTree(array &$nodes): void
    {
        // Sort by weight descending (same-level sort)
        uasort($nodes, function ($a, $b) {
            return ($b['weight'] ?? 0) <=> ($a['weight'] ?? 0);
        });

        // Re-index to continuous array
        $nodes = array_values($nodes);

        // Recursively sort children
        foreach ($nodes as &$node) {
            unset($node['weight']);
            if (!empty($node['children'])) {
                $this->sortTree($node['children']);
            }
        }
        unset($node);
    }

    /**
     * Complete relative urls in tree to absolute urls (add mount prefix)
     * @param array &$tree Tree structure (by reference, modifies original tree)
     * @param string $prefix Mount prefix, e.g. /admin/
     * @return array Completed tree
     */
    public function resolveUrls(array &$tree, string $prefix): array
    {
        $this->walkTree($tree, function (&$node, int $depth) use ($prefix) {
            $url = (string) ($node['url'] ?? '');
            $node['url'] = $prefix . $url;
        });
        return $tree;
    }

    /**
     * Read class docblock (returns '' if not exists/no annotations)
     */
    protected function getClassDoc(string $class): string
    {
        if ($class === '' || !class_exists($class)) {
            return '';
        }
        try {
            return (string) (new \ReflectionClass($class))->getDocComment();
        } catch (\Throwable $e) { // @codeCoverageIgnore
            return ''; // @codeCoverageIgnore
        }
    }

    /**
     * Read method docblock (returns '' if not exists/no annotations)
     */
    protected function getMethodDoc(string $class, string $method): string
    {
        if ($class === '' || !class_exists($class)) {
            return '';
        }
        try {
            return (string) (new \ReflectionMethod($class, $method))->getDocComment();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Parse "@tag Value" line: the value runs to the end of the line and is trimmed,
     * so it may contain blanks ("@menu_action User List" -> "User List").
     *
     * @return string|null null when the annotation is missing or has an empty value
     */
    protected function parseAnnotatedLine(string $doc, string $tag): ?string
    {
        if (!preg_match('/@' . $tag . '\s+([^*\n]+)/', $doc, $m)) {
            return null;
        }
        $value = trim($m[1]);
        if ($value === '') {
            return null;
        }
        return $value;
    }

    /**
     * Parse @menu_weight N, defaults to 0
     */
    protected function parseWeight(string $doc): int
    {
        if (preg_match('/@menu_weight\s+(-?\d+)/', $doc, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    /**
     * Traverse entire tree from root to descendants, execute callback for each node
     *
     * @param array &$nodes Tree structure (by reference, directly modifies original tree)
     * @param callable $callback Callback function with signature function(array &$node, int $depth): void
     *                         - $node: Current node reference, can be modified directly
     *                         - $depth: Current depth, root is 0
     * @return array Returns modified tree (same reference as $nodes)
     */
    public function walkTree(array &$nodes, callable $callback): array
    {
        $this->walkTreeRecursive($nodes, $callback, 0);
        return $nodes;
    }

    /**
     * Internal recursive tree traversal implementation
     *
     * @param array &$nodes Node array (by reference)
     * @param callable $callback Callback function
     * @param int $depth Current depth, root is 0
     */
    protected function walkTreeRecursive(array &$nodes, callable $callback, int $depth): void
    {
        foreach ($nodes as &$node) {
            $callback($node, $depth);
            if (!empty($node['children'])) {
                $this->walkTreeRecursive($node['children'], $callback, $depth + 1);
            }
        }
        unset($node);
    }

    /**
     * Convert permission menu tree to sidebar menu tree
     *
     * @param array $nodes Permission menu tree
     * @return array Sidebar menu tree
     */
    public function permissionMenuTreeToSideMenuTree(array $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $type = $node['type'] ?? 0;
            $isDirectory = ($type === 0);

            // Recursively filter children
            $children = $node['children'] ?? [];
            if (!empty($children)) {
                $children = $this->permissionMenuTreeToSideMenuTree($children);
            }

            // type > 1: skip
            if ($type > 1) {
                continue;
            }
            // type=0 with empty children: skip
            if ($isDirectory && empty($children)) {
                continue;
            }

            // Build node
            $item = [
                'name' => $node['name'] ?? '',
                'url' => $node['url'] ?? '',
                'icon' => $node['icon'] ?? null,
                'type' => $type,
            ];
            if (!empty($children)) {
                $item['children'] = $children;
            }
            $result[] = $item;
        }
        return $result;
    }

    /**
     * Convert recordset (flat data) to tree structure
     *
     * @param array $recordset Recordset, each record contains id and pid (or parent_id)
     * @param string $idField id field name, defaults to 'id'
     * @param string $pidField parent id field name, defaults to 'pid'
     * @param int $rootPid Root node's parent id value, defaults to 0
     * @return array Tree structure
     */
    public function recordsetToTree(array $recordset, string $idField = 'id', string $pidField = 'pid', int $rootPid = 0): array
    {
        // Build id => record map
        $map = [];
        foreach ($recordset as $record) {
            $id = $record[$idField] ?? null;
            if ($id !== null) {
                $map[$id] = $record;
                $map[$id]['children'] = [];
            }
        }

        // Build tree
        $tree = [];
        foreach ($map as $id => &$node) {
            $pid = $node[$pidField] ?? $rootPid;
            if ($pid == $rootPid || !isset($map[$pid])) {
                $tree[] = &$node;
            } else {
                $map[$pid]['children'][] = &$node;
            }
        }
        unset($node);

        return $tree;
    }

    /**
     * Convert tree structure to recordset (flat data)
     *
     * @param array $tree Tree structure
     * @param string $idField id field name, defaults to 'id'
     * @param string $pidField parent id field name, defaults to 'pid'
     * @param int $rootPid Root node's parent id value, defaults to 0
     * @return array Recordset
     */
    public function treeToRecordset(array $tree, string $idField = 'id', string $pidField = 'pid', int $rootPid = 0): array
    {
        $recordset = [];
        $this->treeToRecordsetRecursive($tree, $recordset, $idField, $pidField, $rootPid);
        return $recordset;
    }

    /**
     * Internal recursive implementation of treeToRecordset
     *
     * @param array $nodes Node array
     * @param array &$recordset Recordset (by reference)
     * @param string $idField id field name
     * @param string $pidField parent id field name
     * @param int $pid Parent id
     */
    protected function treeToRecordsetRecursive(array $nodes, array &$recordset, string $idField, string $pidField, int $pid): void
    {
        foreach ($nodes as $node) {
            $id = $node[$idField] ?? null;
            $record = $node;
            unset($record['children']);
            $record[$pidField] = $pid;
            $recordset[] = $record;
            if (!empty($node['children'])) {
                $this->treeToRecordsetRecursive($node['children'], $recordset, $idField, $pidField, $id ?? 0);
            }
        }
    }
}
