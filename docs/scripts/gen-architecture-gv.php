<?php declare(strict_types=1);
/**
 * gen-architecture-gv.php -- generate the architecture diagram source docs/duckphp.gv from src/.
 *
 * Why this exists: docs/duckphp.gv used to be hand written and was last touched in 2024-04.
 * It still contained long-gone namespaces (DuckPhp\Helper / HelperX / HelperY / FastInstaller /
 * Foundation\Core / Foundation\Component) and nodes for already deleted classes, while the current
 * Foundation\Business|Controller|Model|System, GlobalAdmin and GlobalUser were missing entirely.
 * A hand drawn diagram rots; this one is generated. Everything the diagram prints is English
 * (labels, legend, edge labels) so it renders with any font.
 *
 * What is drawn (everything derived from code, no hand kept class list):
 *   - one cluster per namespace, the label carries the type count;
 *   - node shape by declaration: class=box, abstract class=box3d, interface=note, trait=diamond;
 *   - edges by relation: extends=solid, implements=dashed, use <Trait>=dotted, plus a bold blue
 *     "assembles" edge for every ::class mentioned inside initComponents*() (who wires whom up);
 *   - tooltips (visible in the SVG) carry the source file path.
 *
 * Usage:
 *   php docs/scripts/gen-architecture-gv.php            # write docs/duckphp.gv
 *   php docs/scripts/gen-architecture-gv.php --check     # only verify it is current (exit 1 when stale)
 *   php docs/scripts/gen-architecture-gv.php --stdout    # print to stdout
 *
 * Render it (canonical: graphviz; when `dot` is unavailable the WASM build on npm works too):
 *   dot docs/duckphp.gv -T svg -O
 *   npm i @viz-js/viz   # then renderString(src, {format:'svg'}) in Node -- see the script table
 *                       # of docs/zh/reference-maintenance-guide.md
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
                'replaces' => [],
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
            // Replaces-a-singleton edges: `SomeClass::_($this)` / `SomeClass::_(static::_())`.
            // KernelTrait's dynamic forms (`(self::class)::_($this)`, `($options['override_from'])::_($this)`)
            // are not a static class reference, so they are (correctly) not drawn.
            $replacer = new NodeTraverser();
            $replacer->addVisitor(new class ($info['replaces']) extends \PhpParser\NodeVisitorAbstract {
                public function __construct(public array &$out) {}
                public function enterNode(Node $node) {
                    if (!($node instanceof Node\Expr\StaticCall)) {
                        return null;
                    }
                    if (!($node->class instanceof Node\Name) || !($node->name instanceof Node\Identifier)) {
                        return null;
                    }
                    if ($node->name->toString() !== '_' || count($node->args) !== 1) {
                        return null;
                    }
                    $arg = $node->args[0]->value;
                    $is_this = $arg instanceof Node\Expr\Variable && $arg->name === 'this';
                    $is_static_self = $arg instanceof Node\Expr\StaticCall
                        && $arg->class instanceof Node\Name
                        && in_array(strtolower($arg->class->toString()), ['static', 'self'], true)
                        && $arg->name instanceof Node\Identifier
                        && $arg->name->toString() === '_';
                    if ($is_this || $is_static_self) {
                        $this->out[] = $node->class->toString();
                    }
                    return null;
                }
            });
            $replacer->traverse([$node]);

            $info['traits'] = array_values(array_unique($info['traits']));
            $info['implements'] = array_values(array_unique($info['implements']));
            $info['assembles'] = array_values(array_unique($info['assembles']));
            $info['replaces'] = array_values(array_unique($info['replaces']));
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

// keep only targets that really exist in src/ (::class may point to an outside class)
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
$out[] = '    label = "[Legend]";';
$out[] = '    label_legend [label="Shapes: box=class / box3d=abstract class / note=interface / diamond=trait\nEdges: solid=extends / dashed=implements / dotted=use trait / bold blue=assembled in initComponents*() / red dashed=replaces a singleton (X::_($this))",shape="plaintext",fontsize="10"];';
$out[] = '}';
$out[] = '';

foreach ($by_ns as $ns => $items) {
    $out[] = 'subgraph cluster_' . preg_replace('/[^A-Za-z0-9_]/', '_', $ns) . ' {';
    $out[] = '    label = "[' . str_replace('\\', '\\\\', $ns) . '] ' . count($items) . ' types";';
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
                $out[] = '    ' . $id . ' -> ' . $nodeId($a) . ' [style="bold",color="#1f77b4",label="assembles"];';
            }
        }
        foreach ($info['replaces'] as $r) {
            if ($exists($r) && $r !== $info['fqcn']) {
                $out[] = '    ' . $id . ' -> ' . $nodeId($r) . ' [style="dashed",color="#d62728",penwidth=2,label="replaces"];';
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
echo "written: docs/duckphp.gv ($n_ns namespaces, $n_nodes types)\n";
