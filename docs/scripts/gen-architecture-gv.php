<?php declare(strict_types=1);
/**
 * gen-architecture-gv.php —— 从 src/ 生成架构图源文件 docs/duckphp.gv
 *
 * 为什么要有这个脚本：docs/duckphp.gv 原来是手写的，最后一次更新在 2024-04，
 * 里面还留着 DuckPhp\Helper / HelperX / HelperY / FastInstaller / Foundation\Core /
 * Foundation\Component 这些早已不存在的命名空间，而 Foundation\Business|Controller|Model|System、
 * GlobalAdmin、GlobalUser 一个都没画。手写图必然烂，改成生成。
 *
 * 画什么（全部从代码推导，没有人工名单）：
 *   - 一个命名空间一个 cluster，标签带类数；
 *   - 节点形状按声明种类：class=box、abstract class=box3d、interface=note、trait=diamond；
 *   - 边按关系：extends=实线、implements=虚线、use <Trait>=点线；
 *     另有「装配」粗线：initComponents*() 里出现的 ::class 引用（谁把谁装进容器）；
 *   - 悬停提示（SVG 里可见）是该类的源文件路径。
 *
 * 用法：
 *   php docs/scripts/gen-architecture-gv.php            # 写入 docs/duckphp.gv
 *   php docs/scripts/gen-architecture-gv.php --check     # 只校验是否最新（过期退出码 1）
 *   php docs/scripts/gen-architecture-gv.php --stdout    # 打印到标准输出
 *
 * 渲染成 SVG（本仓用 dot；没装 graphviz 时可用 npm 上的 WASM 版 graphviz）：
 *   dot docs/duckphp.gv -T svg -O
 *   npx -y @viz-js/viz ...   # 见 docs/zh/reference-maintenance-guide.md 的脚本表
 */
require __DIR__ . '/../../vendor/autoload.php';

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

$root = realpath(__DIR__ . '/../..');
$src_dir = $root . '/src';
$target = $root . '/docs/duckphp.gv';

$args = array_slice($argv, 1);
$check = in_array('--check', $args, true);
$stdout = in_array('--stdout', $args, true);

$parser = (new ParserFactory())->createForNewestSupportedVersion();

/**
 * @return array<string, array<string, mixed>> fqcn => info
 */
function collectClasses(string $src_dir, $parser): array
{
    $classes = [];
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src_dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    sort($files);

    foreach ($files as $path) {
        $code = file_get_contents($path);
        $ast = $parser->parse($code);
        if ($ast === null) {
            continue;
        }
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $ast = $traverser->traverse($ast);

        $rel = str_replace(dirname($src_dir) . '/', '', $path);
        foreach (findClassLikes($ast) as $node) {
            if (!($node instanceof Node\Stmt\ClassLike)) {
                continue;
            }
            $fqcn = isset($node->namespacedName) ? $node->namespacedName->toString() : (string) $node->name;
            $info = [
                'kind' => 'class',
                'abstract' => false,
                'file' => $rel,
                'extends' => null,
                'implements' => [],
                'traits' => [],
                'assembles' => [],
            ];
            if ($node instanceof Node\Stmt\Interface_) {
                $info['kind'] = 'interface';
            } elseif ($node instanceof Node\Stmt\Trait_) {
                $info['kind'] = 'trait';
            } elseif ($node instanceof Node\Stmt\Class_) {
                $info['abstract'] = $node->isAbstract();
                if ($node->extends !== null) {
                    $info['extends'] = $node->extends->toString();
                }
                foreach ($node->implements as $i) {
                    $info['implements'][] = $i->toString();
                }
            }
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\TraitUse) {
                    foreach ($stmt->traits as $t) {
                        $info['traits'][] = $t->toString();
                    }
                }
                if ($stmt instanceof Node\Stmt\ClassMethod && stripos($stmt->name->toString(), 'initcomponents') === 0) {
                    $finder = new NodeTraverser();
                    $finder->addVisitor(new class ($info['assembles']) extends \PhpParser\NodeVisitorAbstract {
                        public function __construct(public array &$out) {}
                        public function enterNode(Node $node) {
                            if ($node instanceof Node\Expr\ClassConstFetch
                                && $node->name instanceof Node\Identifier
                                && $node->name->toString() === 'class'
                                && $node->class instanceof Node\Name) {
                                $this->out[] = $node->class->toString();
                            }
                            return null;
                        }
                    });
                    $finder->traverse([$stmt]);
                }
            }
            $info['traits'] = array_values(array_unique($info['traits']));
            $info['implements'] = array_values(array_unique($info['implements']));
            $info['assembles'] = array_values(array_unique($info['assembles']));
            $classes[$fqcn] = $info;
        }
    }
    return $classes;
}

/**
 * Class/interface/trait declarations live inside a Namespace_ node, so walk one level down.
 *
 * @param array<Node\Stmt> $stmts
 * @return array<Node\Stmt\ClassLike>
 */
function findClassLikes(array $stmts): array
{
    $ret = [];
    foreach ($stmts as $node) {
        if ($node instanceof Node\Stmt\ClassLike) {
            $ret[] = $node;
        } elseif ($node instanceof Node\Stmt\Namespace_) {
            $ret = array_merge($ret, findClassLikes($node->stmts));
        } elseif ($node instanceof Node\Stmt\If_) {
            // DuckPhpAllInOne style conditional declarations are not used here, but be safe.
            $ret = array_merge($ret, findClassLikes($node->stmts));
        }
    }
    return $ret;
}

$classes = collectClasses($src_dir, $parser);

// 只保留 src/ 里真实存在的目标（::class 可能指向外部类）
$exists = static fn(string $fqcn): bool => isset($classes[$fqcn]);
$nodeId = static fn(string $fqcn): string => str_replace('\\', '_', $fqcn);

ksort($classes);
$by_ns = [];
foreach ($classes as $fqcn => $info) {
    $pos = strrpos($fqcn, '\\');
    $ns = $pos === false ? '(global)' : substr($fqcn, 0, $pos);
    $short = $pos === false ? $fqcn : substr($fqcn, $pos + 1);
    $by_ns[$ns][$short] = $info + ['fqcn' => $fqcn];
}
ksort($by_ns);

$shape = static function (array $info): string {
    if ($info['kind'] === 'interface') {
        return 'note';
    }
    if ($info['kind'] === 'trait') {
        return 'diamond';
    }
    return $info['abstract'] ? 'box3d' : 'box';
};

$out = [];
$out[] = 'digraph DuckPhp{';
$out[] = '/* This is a graphviz file. GENERATED by docs/scripts/gen-architecture-gv.php -- do not edit by hand. */';
$out[] = '/* Render: dot docs/duckphp.gv -T svg -O */';
$out[] = 'graph [rankdir = "LR";fontsize="12";newrank=true;];';
$out[] = 'node [fontsize="10";shape="box"];';
$out[] = 'edge [fontsize="9"];';
$out[] = '';

$out[] = 'subgraph cluster_legend {';
$out[] = '    label = "[图例]";';
$out[] = '    label_legend [label="形状：box=类 / box3d=抽象类 / note=接口 / diamond=trait\n边：实线=extends / 虚线=implements / 点线=use trait / 粗线=initComponents*() 装配",shape="plaintext",fontsize="10"];';
$out[] = '}';
$out[] = '';

foreach ($by_ns as $ns => $items) {
    $out[] = 'subgraph cluster_' . preg_replace('/[^A-Za-z0-9_]/', '_', $ns) . ' {';
    $out[] = '    label = "[' . str_replace('\\', '\\\\', $ns) . '] ' . count($items) . ' 个";';
    $out[] = '';
    ksort($items);
    foreach ($items as $short => $info) {
        $attrs = ['label="' . $short . '"', 'shape="' . $shape($info) . '"', 'tooltip="' . $info['file'] . '"'];
        $out[] = '    ' . $nodeId($info['fqcn']) . ' [' . implode(',', $attrs) . '];';
    }
    $out[] = '';
    foreach ($items as $short => $info) {
        $id = $nodeId($info['fqcn']);
        if ($info['extends'] !== null && $exists($info['extends'])) {
            $out[] = '    ' . $id . ' -> ' . $nodeId($info['extends']) . ' [style="solid"];';
        }
        foreach ($info['implements'] as $i) {
            if ($exists($i)) {
                $out[] = '    ' . $id . ' -> ' . $nodeId($i) . ' [style="dashed"];';
            }
        }
        foreach ($info['traits'] as $t) {
            if ($exists($t)) {
                $out[] = '    ' . $id . ' -> ' . $nodeId($t) . ' [style="dotted"];';
            }
        }
        foreach ($info['assembles'] as $a) {
            if ($exists($a) && $a !== $info['fqcn']) {
                $out[] = '    ' . $id . ' -> ' . $nodeId($a) . ' [style="bold",color="#1f77b4",label="装配"];';
            }
        }
    }
    $out[] = '}';
    $out[] = '';
}
$out[] = '}';

$text = implode("\n", $out) . "\n";

if ($stdout) {
    echo $text;
    exit(0);
}
if ($check) {
    $current = is_file($target) ? file_get_contents($target) : '';
    if ($current !== $text) {
        fwrite(STDERR, "docs/duckphp.gv is stale: run php docs/scripts/gen-architecture-gv.php\n");
        exit(1);
    }
    echo "docs/duckphp.gv is up to date\n";
    exit(0);
}
file_put_contents($target, $text);
$n_nodes = count($classes);
$n_ns = count($by_ns);
echo "written: docs/duckphp.gv ($n_ns 个命名空间, $n_nodes 个类型)\n";
