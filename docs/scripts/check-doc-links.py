#!/usr/bin/env python3
"""Check every relative *.md link under docs/ (default) and report the missing ones.

Usage:  python3 scripts/check-doc-links.py [dir ...]

Also reports markdown files that are NOT valid UTF-8 (they cannot be parsed for
links, and Chinese text written with a non-UTF-8 codepage shows as mojibake).

Exit code is always 0 (like scripts/check-non-ascii.sh): read the printed
"broken: N" line instead. External links, anchors and non-md targets are ignored.
"""
import glob
import io
import os
import re
import sys

roots = sys.argv[1:] or ['docs']
bad = []
not_utf8 = []
total = 0

for root in roots:
    for path in sorted(glob.glob(root + '/**/*.md', recursive=True)):
        base = os.path.dirname(path)
        try:
            text = io.open(path, encoding='utf-8').read()
        except UnicodeDecodeError as exc:
            not_utf8.append((path, str(exc)))
            continue
        for target in re.findall(r'\]\(([^)\s]+)\)', text):
            if target.startswith(('http://', 'https://', 'mailto:', '#')):
                continue
            target = target.split('#')[0]
            if not target or not target.endswith('.md'):
                continue
            total += 1
            if not os.path.exists(os.path.normpath(os.path.join(base, target))):
                bad.append((path, target))

print('checked %d relative md links, broken: %d' % (total, len(bad)))
for path, target in bad:
    print('  %s -> %s' % (path, target))

if not_utf8:
    print('not valid UTF-8: %d file(s) -- links inside them were NOT checked' % len(not_utf8))
    for path, exc in not_utf8:
        print('  %s (%s)' % (path, exc))
