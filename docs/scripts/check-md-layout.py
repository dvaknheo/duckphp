#!/usr/bin/env python3
"""Tell whether a markdown file's working-tree change is *layout only*.

Why: editors and AI tools like to re-pad markdown tables (Obsidian's Advanced Tables
format-on-save, VS Code formatters, agents rewriting a table they touched). Such a diff
is noise and must not be mistaken for a content change -- nor committed silently.

Usage (run from the repository root, WSL or Windows python both fine):
    python3 docs/scripts/check-md-layout.py                 # all modified tracked md under docs/
    python3 docs/scripts/check-md-layout.py --all           # every tracked md under docs/
    python3 docs/scripts/check-md-layout.py docs/zh/guide/a.md [...]

Output per file:
    identical     working tree matches HEAD
    layout-only   the change disappears after normalising whitespace / table padding /
                  empty extra cells -> safe to `git checkout -- <file>`
    CONTENT       something really changed -> review the diff
    new           untracked file, nothing to compare

Normalisation: drop all whitespace; collapse table separator rows (`|---|---|`) to one token;
drop empty table cells (an editor that adds a stray empty column or empty row then compares equal).
"""
import os
import re
import subprocess
import sys


def git(*args):
    out = subprocess.run(['git'] + list(args), capture_output=True)
    if out.returncode != 0:
        sys.exit('git %s failed: %s' % (' '.join(args), out.stderr.decode('utf-8', 'replace').strip()))
    return out.stdout.decode('utf-8')


def normalize(text):
    text = re.sub(r'(?m)^\s*\|[\s|:-]+\|\s*$', '|SEP|', text)   # table separator row
    lines = []
    for line in text.split('\n'):
        if line.lstrip().startswith('|'):
            cells = [c for c in (c.strip() for c in line.strip().strip('|').split('|')) if c]
            line = '|' + '|'.join(cells) + '|'
        lines.append(line)
    return re.sub(r'\s+', '', '\n'.join(lines))


def target_files(argv):
    paths = [a for a in argv if not a.startswith('-')]
    if paths:
        return paths
    if '--all' in argv:
        return [p for p in git('ls-files', 'docs').split('\n') if p.endswith('.md')]
    changed = git('status', '--porcelain', '--', 'docs').split('\n')
    return [l[3:].strip() for l in changed if l[:2].strip() in ('M', 'MM', 'AM') and l[3:].strip().endswith('.md')]


def main():
    files = sorted(set(target_files(sys.argv[1:])))
    if not files:
        print('no modified markdown under docs/ -- nothing to check')
        return
    layout, content, new, same = [], [], [], []
    for f in files:
        if not os.path.exists(f):
            continue
        if subprocess.run(['git', 'cat-file', '-e', 'HEAD:' + f], capture_output=True).returncode != 0:
            new.append(f)
            continue
        head = git('show', 'HEAD:' + f)
        work = open(f, encoding='utf-8').read()
        if head == work:
            same.append(f)
        elif normalize(head) == normalize(work):
            layout.append(f)
        else:
            content.append(f)
    print('checked %d file(s): identical %d, layout-only %d, CONTENT %d, new %d'
          % (len(files), len(same), len(layout), len(content), len(new)))
    for f in layout:
        print('  layout-only  %s' % f)
    for f in content:
        print('  CONTENT      %s' % f)
    for f in new:
        print('  new          %s' % f)


if __name__ == '__main__':
    main()
