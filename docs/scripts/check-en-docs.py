#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""English docs checker: verify docs/en mirrors docs/zh without losing structure.

Usage:
    python3 docs/scripts/check-en-docs.py docs/en/guide/route-hooks.md   # one (or more) files
    python3 docs/scripts/check-en-docs.py --all                          # every translated file
    python3 docs/scripts/check-en-docs.py --cjk docs/en/guide/*.md        # CJK check only
    python3 docs/scripts/check-en-docs.py --missing                      # zh files with no English twin

What it checks, per file:
    * CJK left outside code fences (error) and inside them (warning: should be deliberate);
    * code fences: same count, and the same sequence of non-comment statements;
    * tables: same number of table rows, same column count per row;
    * headings: same count and same level sequence;
    * relative links: target exists in docs/en (else "not translated yet" warning) and,
      when it has a #fragment, that fragment matches a heading slug in the target.

Exit code: number of errors (0 = clean). Warnings never fail the run.
"""
import glob
import io
import os
import re
import sys

CJK = re.compile(r'[\u3000-\u303f\u3400-\u4dbf\u4e00-\u9fff\uff00-\uffef]')

# Files where Chinese outside code fences is deliberate: a language switcher, or term-mapping
# tables that keep the Chinese term next to its English rendering. CJK there is a warning only.
ALLOW_CJK_FILES = {
    'docs/en/index.md',
    'docs/en/guide/appendix-glossary.md',
    'docs/en/guide/appendix-migration.md',
}
FENCE = re.compile(r'^\s*```')
LINK = re.compile(r'\]\(([^)\s]+)\)')
HEADING = re.compile(r'^(#{1,6})\s+(.*)$')
TABLE_ROW = re.compile(r'^\s*\|.*\|\s*$')


def strip_comment(line):
    """Best effort: drop //, #, /* */ and /** */ comments so translated comments don't fail parity."""
    s = line.strip()
    if s.startswith(('//', '#', '*', '<!--', '/*')):
        return ''
    s = re.sub(r'\s+//.*$', '', s)
    s = re.sub(r'\s+#\s.*$', '', s)
    s = re.sub(r'/\*.*?\*/\s*$', '', s)
    return s.strip()


def fence_parity(zh_body, en_body):
    """Line-aligned comparison inside a fence.

    A line whose Chinese counterpart contains CJK is *translatable content* (comments, docblocks,
    example strings, ASCII-art annotations): it is skipped. Every other line is pure code and must
    match byte-for-byte. Different line counts inside the fence are always an error.
    """
    zh_lines = zh_body.split('\n')
    en_lines = en_body.split('\n')
    errs = []
    if len(zh_lines) != len(en_lines):
        errs.append('fence line count %d (en) vs %d (zh)' % (len(en_lines), len(zh_lines)))
        return errs
    for i, (zl, el) in enumerate(zip(zh_lines, en_lines), 1):
        zs, es = strip_comment(zl), strip_comment(el)
        if not zs and not es:
            continue
        if CJK.search(zs):
            if not es:
                errs.append('fence L%d: code line dropped (zh had translatable content, en is a comment)' % i)
            continue
        if es != zs:
            errs.append('fence L%d: code differs\n        zh: %s\n        en: %s' % (i, zs[:90], es[:90]))
    return errs


def split_fences(text):
    """-> (outside_text, [fence_body, ...])"""
    outside, fences, cur, in_fence = [], [], None, False
    for line in text.split('\n'):
        if FENCE.match(line):
            if in_fence:
                fences.append(cur)
                cur, in_fence = None, False
            else:
                cur, in_fence = [], True
            continue
        if in_fence:
            cur.append(line)
        else:
            outside.append(line)
    if cur is not None:
        fences.append(cur)
    return '\n'.join(outside), ['\n'.join(f) for f in fences]


def slug(text):
    s = text.strip().lower()
    s = re.sub(r'`([^`]*)`', r'\1', s)
    s = re.sub(r'[^\w\s\u4e00-\u9fff-]', '', s)
    return re.sub(r'\s+', '-', s).strip('-')


def headings(text):
    out = []
    for line in text.split('\n'):
        m = HEADING.match(line)
        if m:
            out.append((len(m.group(1)), m.group(2)))
    return out


def table_shape(text):
    rows = []
    for line in text.split('\n'):
        if TABLE_ROW.match(line):
            cells = [c for c in line.strip().strip('|').split('|')]
            rows.append(len(cells))
    return rows


def zh_twin(path):
    rel = os.path.relpath(path, 'docs/en')
    return os.path.join('docs/zh', rel)


def check(path, cjk_only=False):
    errors, warnings = [], []
    text = io.open(path, encoding='utf-8').read()
    outside, fences = split_fences(text)

    # CJK
    allow_cjk = path.replace('\\', '/') in ALLOW_CJK_FILES
    for i, line in enumerate(text.split('\n'), 1):
        if CJK.search(line):
            is_fence = any(CJK.search(f) and line in f for f in fences)
            if is_fence:
                warnings.append('L%d: inside fence CJK: %s' % (i, line.strip()[:80]))
            elif allow_cjk:
                warnings.append('L%d: deliberate CJK (allowlisted file): %s' % (i, line.strip()[:80]))
            else:
                errors.append('L%d: outside fence CJK: %s' % (i, line.strip()[:80]))
    if cjk_only:
        return errors, warnings

    twin = zh_twin(path)
    if not os.path.exists(twin):
        warnings.append('no Chinese twin at %s' % twin)
    else:
        ztext = io.open(twin, encoding='utf-8').read()
        zoutside, zfences = split_fences(ztext)

        if len(fences) != len(zfences):
            errors.append('code fences: en has %d, zh has %d' % (len(fences), len(zfences)))
        else:
            for n, (en_f, zh_f) in enumerate(zip(fences, zfences)):
                for e in fence_parity(zh_f, en_f):
                    errors.append('block #%d %s' % (n + 1, e))

        if len(table_shape(text)) != len(table_shape(ztext)):
            errors.append('table rows: en has %d, zh has %d'
                          % (len(table_shape(text)), len(table_shape(ztext))))
        if len(headings(text)) != len(headings(ztext)):
            errors.append('headings: en has %d, zh has %d' % (len(headings(text)), len(headings(ztext))))
        else:
            for (el, _), (zl, _) in zip(headings(text), headings(ztext)):
                if el != zl:
                    errors.append('heading level mismatch (en #%d vs zh #%d)' % (el, zl))

    # links + anchors
    base = os.path.dirname(path)
    for m in LINK.finditer(text):
        target = m.group(1)
        if target.startswith(('http', 'mailto:')):
            continue
        file_part, _, frag = target.partition('#')
        if file_part:
            resolved = os.path.normpath(os.path.join(base, file_part))
            if not os.path.exists(resolved):
                warnings.append('link target missing (not translated yet?): %s' % target)
                continue
            target_text = io.open(resolved, encoding='utf-8').read()
        else:
            target_text = text
        if frag:
            if frag not in [slug(h[1]) for h in headings(target_text)]:
                errors.append('anchor #%s not found in %s' % (frag, file_part or os.path.basename(path)))
    return errors, warnings


def main():
    args = [a for a in sys.argv[1:] if not a.startswith('--')]
    flags = [a for a in sys.argv[1:] if a.startswith('--')]
    if '--missing' in flags:
        missing = []
        for zh in sorted(glob.glob('docs/zh/**/*.md', recursive=True)):
            if not os.path.exists(os.path.join('docs/en', os.path.relpath(zh, 'docs/zh'))):
                missing.append(zh)
        print('zh files without an English twin: %d' % len(missing))
        for m in missing:
            print('   ', m)
        return 0
    if '--all' in flags:
        files = [f for f in sorted(glob.glob('docs/en/**/*.md', recursive=True))
                 if os.path.basename(f) != 'TRANSLATION.md']
    else:
        files = args
    if not files:
        print(__doc__)
        return 0

    total_errors = 0
    for path in files:
        errors, warnings = check(path, cjk_only='--cjk' in flags)
        total_errors += len(errors)
        status = 'FAIL' if errors else ('warn' if warnings else 'ok  ')
        print('%s %s' % (status, path))
        for e in errors:
            print('     ERROR %s' % e)
        for w in warnings[:6]:
            print('     warn  %s' % w)
        if len(warnings) > 6:
            print('     warn  ... %d more warnings' % (len(warnings) - 6))
    print('--- %d file(s), %d error(s)' % (len(files), total_errors))
    return total_errors


if __name__ == '__main__':
    sys.exit(min(main(), 120))
