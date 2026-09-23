#!/usr/bin/env python3
"""Reverse-link check: reference pages that the user guide never links to.

Usage:
    python3 docs/scripts/find-unmentioned-classes.py [--all] [--json]
                                                     [--reference DIR] [--guide DIR]

How it decides (link-only, no text matching)
    * "a class" = a page under docs/zh/reference/ whose H1 is `# DuckPhp\A\B`
      (index.md / options.md / options-by-class.md / options-index.md / setting.md
       are skipped, they are not class pages).
    * "mentioned in the guide" = some docs/zh/guide/*.md contains a markdown link
      whose target resolves to that page file, e.g.
          [`Ext\\MiniRoute`](../reference/Ext-MiniRoute.md)
          [DuckPhp\\Core\\App](../reference/Core-App.md#init)
      Anchors and `./`/`../` prefixes are normalised away; a page basename is
      unique, so the lookup is by target basename.
    * So this is deliberately conservative: writing the class name in prose without
      linking it does NOT count. Run --all to see the pages that are linked only
      once, which are the weakest claims.

Output
    A summary line, then the pages never linked from the guide, grouped by area.
    --all also lists pages linked exactly once (single, easy-to-miss mention).
    --json dumps the same facts as JSON.

Exit code is always 0 (same convention as check-doc-links.py): read the counts.
"""
import glob
import io
import json
import os
import re
import sys

REF_DIR = 'docs/zh/reference'
GUIDE_DIR = 'docs/zh/guide'
SKIP_PAGES = ('index.md', 'options.md', 'options-by-class.md', 'options-index.md', 'setting.md')

args = sys.argv[1:]
show_all = '--all' in args
as_json = '--json' in args
for flag, attr in (('--reference', 'REF_DIR'), ('--guide', 'GUIDE_DIR')):
    if flag in args:
        globals()[attr] = args[args.index(flag) + 1]

LINK_RE = re.compile(r'\[[^\]]*\]\(([^)\s]+)')

# ---------------------------------------------------------------- 参考页的类
classes = []
for path in sorted(glob.glob(REF_DIR + '/*.md')):
    name = os.path.basename(path)
    if name in SKIP_PAGES:
        continue
    text = io.open(path, encoding='utf-8').read()
    m = re.match(r'#\s+(DuckPhp(?:\\\w+)+)\s*$', text.split('\n', 1)[0].strip())
    if not m:
        continue
    fqn = m.group(1)
    classes.append({
        'fqn': fqn,
        'page': name,
        'area': fqn.split('\\')[1] if len(fqn.split('\\')) > 2 else '(根)',
        'from': [],          # 链接到它的指南文件
    })
by_page = {c['page']: c for c in classes}

# ---------------------------------------------------------------- 反查指南链接
guide_files = sorted(glob.glob(GUIDE_DIR + '/*.md'))
n_links = 0
for path in guide_files:
    for target in LINK_RE.findall(io.open(path, encoding='utf-8').read()):
        base = os.path.basename(target.split('#', 1)[0])
        hit = by_page.get(base)
        if not hit:
            continue
        n_links += 1
        if os.path.basename(path) not in hit['from']:
            hit['from'].append(os.path.basename(path))

unlinked = [c for c in classes if not c['from']]
once = [c for c in classes if len(c['from']) == 1]
linked = [c for c in classes if c['from']]


def area_order(area):
    fixed = ['Core', 'Component', 'Db', 'Ext', 'Foundation', 'GlobalAdmin', 'GlobalUser', 'HttpServer']
    return (fixed.index(area) if area in fixed else len(fixed), area)


def dump(items, extra=False):
    for area in sorted({c['area'] for c in items}, key=area_order):
        print('  -- %s' % area)
        for c in sorted((x for x in items if x['area'] == area), key=lambda x: x['fqn']):
            if extra:
                print('   %-58s %s' % (c['fqn'], ', '.join(c['from'])))
            else:
                print('   %-58s %s' % (c['fqn'], c['page']))


if as_json:
    print(json.dumps({
        'classes': len(classes),
        'guide_files': len(guide_files),
        'guide_to_reference_links': n_links,
        'linked': len(linked),
        'unlinked': [c['fqn'] for c in unlinked],
        'linked_once': {c['fqn']: c['from'][0] for c in once},
    }, ensure_ascii=False, indent=2))
    sys.exit(0)

print('参考页类数: %d ；用户指南: %d 个 md ；指南→参考页链接: %d 条' % (len(classes), len(guide_files), n_links))
print('  被指南链到: %d （其中只链 1 次的 %d）；从没被链到: %d' % (len(linked), len(once), len(unlinked)))

print('\n== 指南从没链到（%d）==' % len(unlinked))
dump(unlinked)

if show_all:
    print('\n== 指南只链了 1 次（%d，最容易被漏掉的）==' % len(once))
    dump(once, extra=True)
