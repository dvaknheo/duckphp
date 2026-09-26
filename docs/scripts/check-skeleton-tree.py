#!/usr/bin/env python3
"""check-skeleton-tree.py — 让 `skeleton/AGENTS.md` §1 的目录树与实际文件保持一致。

背景：骨架侧曾经有四份目录树（`RULES.md`、`agent-zh.md` ×2、README ×2），一改骨架就漂
（`cli.php` 写成根目录、`view/test/done.php` 漏掉……）。现在目录树只剩 `skeleton/AGENTS.md`
一份，这个脚本负责盯住它：

  1. 树里的每个条目都必须真实存在（`skeleton/` 下）；
  2. `skeleton/` 下的每个文件都必须在树里出现（`runtime/` 里的运行期文件忽略）；
  3. 文档里指向随包文档的指针（`vendor/dvaknheo/duckphp/<路径>`）在仓库里真实存在。

用法（从仓库根目录）：
    python3 docs/scripts/check-skeleton-tree.py          # 检查，0 = 一致，1 = 有差异
    python3 docs/scripts/check-skeleton-tree.py --fix    # 就地修正：删掉失效条目、补上缺失条目
    python3 docs/scripts/check-skeleton-tree.py -v       # 打印解析出的条目

忽略规则（第 2 条）：`runtime/` 下的文件是运行期产物，不必进树。
"""

import io
import os
import re
import sys

ROOT = os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', '..'))
SKELETON = os.path.join(ROOT, 'skeleton')
AGENTS = os.path.join(SKELETON, 'AGENTS.md')

# 磁盘上有、但不必进树的文件（前缀匹配）
IGNORE_DISK = ('runtime/',)

ENTRY_RE = re.compile(r'^(?P<indent>(?:│   |    )*)(?:├── |└── )(?P<name>.+?)\s*$')
FENCE_RE = re.compile(r'^```')


def read_lines():
    return io.open(AGENTS, encoding='utf-8').read().split('\n')


def find_tree_block(lines):
    """返回 (start, end)：```text 围栏内那几行的下标区间（不含围栏行本身）。"""
    start = None
    for i, line in enumerate(lines):
        if FENCE_RE.match(line):
            if start is None:
                start = i
            else:
                return start + 1, i
    raise SystemExit('AGENTS.md: 找不到目录树围栏')


def parse_entries(lines, start, end):
    """解析树，返回条目列表：dict(indent/line/path/is_dir/raw)。"""
    entries = []
    stack = []  # [(indent_len, path)]
    for i in range(start, end):
        m = ENTRY_RE.match(lines[i])
        if not m:
            continue
        indent = len(m.group('indent'))
        raw = m.group('name')
        name = raw.split('#')[0].strip()
        if not name:
            continue
        sample = name.startswith('* ')
        if sample:
            name = name[2:].strip()
        while stack and stack[-1][0] >= indent:
            stack.pop()
        path = (stack[-1][1] + '/' + name) if stack else name
        is_dir = path.endswith('/')
        path = path.rstrip('/')
        entries.append({
            'line': i,
            'indent': indent,
            'path': path,
            'is_dir': is_dir,
            'sample': sample,
            'raw': raw,
        })
        if is_dir:
            stack.append((indent, path))
    return entries


def disk_paths():
    files, dirs = [], []
    for base, subdirs, names in os.walk(SKELETON):
        rel_base = os.path.relpath(base, SKELETON).replace('\\', '/')
        rel_base = '' if rel_base == '.' else rel_base
        for d in subdirs:
            dirs.append((rel_base + '/' + d).lstrip('/'))
        for n in names:
            rel = (rel_base + '/' + n).lstrip('/')
            if any(rel.startswith(p) for p in IGNORE_DISK):
                continue
            files.append(rel)
    return sorted(files), sorted(dirs)


def pointer_check():
    """文档里 `vendor/dvaknheo/duckphp/<路径>` 这样的指针必须在仓库里存在。"""
    problems = []
    targets = []
    text = io.open(AGENTS, encoding='utf-8').read()
    for m in re.finditer(r'vendor/dvaknheo/duckphp/([A-Za-z0-9_./-]+)', text):
        target = m.group(1).rstrip('/')
        targets.append(target)
        if not os.path.exists(os.path.join(ROOT, target)):
            problems.append('指针不存在：vendor/dvaknheo/duckphp/%s -> %s' % (m.group(1), target))
    return problems, targets


def fix(lines, entries):
    """删掉失效条目、按目录分组补上缺失条目。不改动其它行的字形/注释，因此可反复跑。"""
    files, _dirs = disk_paths()
    have = set(e['path'] for e in entries)
    stale = [e for e in entries if not os.path.exists(os.path.join(SKELETON, e['path']))]
    missing = [f for f in files if f not in have]
    drop = set(e['line'] for e in stale)

    rows = [{'src': i, 'text': ln} for i, ln in enumerate(lines) if i not in drop]

    def pos_of(src_line):
        for k, row in enumerate(rows):
            if row['src'] == src_line:
                return k
        return None

    for rel in missing:
        parent = os.path.dirname(rel)
        anchor = None
        for e in entries:
            if e['is_dir'] and e['path'] == parent:
                anchor = e
        if anchor is None:
            print('  ! 跳过（树里没有父目录条目）: %s' % rel)
            continue
        children = []
        for e in entries:
            if e['line'] <= anchor['line']:
                continue
            if e['indent'] <= anchor['indent']:
                break
            children.append(e)
        last_src = children[-1]['line'] if children else anchor['line']
        k = pos_of(last_src)
        if k is None:
            continue
        if rows[k]['text'].lstrip().startswith('└── '):
            rows[k]['text'] = rows[k]['text'].replace('└── ', '├── ', 1)
        rows.insert(k + 1, {'src': None, 'text': '%s└── %s'
                            % (' ' * (anchor['indent'] + 4), os.path.basename(rel))})
        print('  + 补上 %s' % rel)

    for e in stale:
        print('  - 删掉不存在的条目 %s' % e['path'])

    io.open(AGENTS, 'w', encoding='utf-8', newline='').write(
        '\n'.join(row['text'] for row in rows))
    return len(stale) + len(missing)


def main():
    args = sys.argv[1:]
    do_fix = '--fix' in args
    verbose = '-v' in args or '--verbose' in args

    lines = read_lines()
    start, end = find_tree_block(lines)
    entries = parse_entries(lines, start, end)
    files, _dirs = disk_paths()

    listed_files = set(e['path'] for e in entries if not e['is_dir'])
    listed_dirs = set(e['path'] for e in entries if e['is_dir'])

    missing_on_disk = sorted(e['path'] for e in entries
                             if not os.path.exists(os.path.join(SKELETON, e['path'])))
    not_in_tree = sorted(f for f in files if f not in listed_files)
    dirs_missing = sorted(d for d in listed_dirs
                          if not os.path.isdir(os.path.join(SKELETON, d)))
    pointers, pointer_targets = pointer_check()

    if verbose:
        print('树里 %d 个条目（文件 %d、目录 %d）；磁盘上 %d 个文件'
              % (len(entries), len(listed_files), len(listed_dirs), len(files)))
        for e in entries:
            print('  %s%s' % (e['path'], '/' if e['is_dir'] else ''))

    problems = 0
    for p in missing_on_disk:
        print('树里有、磁盘没有: %s' % p)
        problems += 1
    for d in dirs_missing:
        print('树里有、磁盘没有（目录）: %s' % d)
        problems += 1
    for f in not_in_tree:
        print('磁盘有、树里没有: %s' % f)
        problems += 1
    for p in pointers:
        print(p)
        problems += 1

    if problems and do_fix:
        fixed = fix(lines, entries)
        print('--fix: 处理了 %d 处（指针问题需手工改）' % fixed)
        return 0

    if problems:
        print('check-skeleton-tree: %d 处不一致（用 --fix 处理树；指针问题手工改）' % problems)
        return 1

    print('check-skeleton-tree: 一致（%d 个条目，磁盘 %d 个文件，指针 %d 条）'
          % (len(entries), len(files), len(pointer_targets)))
    return 0


if __name__ == '__main__':
    sys.exit(main())
