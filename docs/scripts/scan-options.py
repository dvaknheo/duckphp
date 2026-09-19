#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
选项扫描器 —— 把 src 里的选项分四类摊开：

  1) declared  正式选项：$options / $core_options / $kernel_options / $common_options
  2) hidden    隐藏选项：$hidden_options（容错 $hiden_options 这类拼写变体）
                 —— 框架会读、但不进正式选项表，只给文档/高级用户看
  3) implicit  隐式选项：成员访问 ->options['x'] 读了，但既没声明也没进隐藏表
  4) setting   设置键：Setting()/ _Setting('x') 读的键（另一套机制）

用法：
  python3 scripts/scan-options.py            # 人类可读报告
  python3 scripts/scan-options.py --json     # 机器可读
退出码：0 = 无异常；1 = 有告警（可当 CI 门禁）

作者备注：本脚本只做静态扫描，不执行 PHP。规则与 docs/zh/reference 的选项页保持一致。
"""
import glob
import io
import json
import os
import re
import sys

SRC = 'src'
OPT_VARS = ['options', 'core_options', 'kernel_options', 'common_options']
HIDDEN_VARS = ['hidden_options', 'hiden_options']          # 后者是常见拼写变体，容错识别并告警
VAR_RE = re.compile(r'\$(\w+)\s*=\s*\[', )


def top_level_keys(block):
    """从一个数组字面量块里取顶层键（缩进 <= 8，跳过注释行）"""
    keys = []
    for line in block.split('\n'):
        s = line.strip()
        if not s or s.startswith('//') or s.startswith('*') or s.startswith('/*'):
            continue
        if len(line) - len(line.lstrip()) > 8:
            continue
        m = re.match(r"'([^']+)'\s*=>", s)
        if m:
            keys.append(m.group(1))
    return keys


def extract_array(php, var):
    """取出 $var = [ ... ]; 的块（到行首缩进的 ]; 为止）；找不到返回 None"""
    m = re.search(r'\$' + var + r'\s*=\s*\[(.*?)\n\s*\];', php, re.S)
    if not m:
        return None
    return top_level_keys(m.group(1))


def php_literal_norm(txt):
    """把 PHP 字面量归一成可比较的字符串；非字面量（表达式）返回 None"""
    t = txt.strip().rstrip(',;').strip()
    low = t.lower()
    if low in ('true', 'false', 'null'):
        return low
    if t == "''":
        return "''"
    if re.fullmatch(r"'-?[^']*'", t) or re.fullmatch(r'-?\d+(\.\d+)?', t):
        return t
    if re.fullmatch(r'\\\\?[A-Za-z_\\][\w\\]*::class', t):   # SomeClass::class
        return t + ' (class)'
    return None          # 表达式（?? 链、函数调用…）


def parse_fallback(raw):
    """从 `?? 右侧` 原文里抠出「最终兜底值」：括号内、取 ?? 链最右端、只认字面量/类名"""
    r = raw.strip().rstrip(',')
    if r.startswith('('):
        depth = 0
        for i, ch in enumerate(r):
            if ch == '(':
                depth += 1
            elif ch == ')':
                depth -= 1
                if depth == 0:
                    inner = r[1:i]
                    if '??' in inner:
                        return parse_fallback(re.split(r'\?\?', inner)[-1])
                    return parse_fallback(inner)
        return (None, None)
    m = re.match(r"(true|false|null|'(?:[^']*)'|-?\d+(?:\.\d+)?|\\\\?[\w\\]+::class)", r)
    if not m:
        return (None, None)
    lit = m.group(1)
    norm = php_literal_norm(lit)
    return (norm if norm is not None else lit, lit)


def main():
    json_mode = '--json' in sys.argv

    declared = {}      # class => {key: var}
    hidden = {}        # class => {key: [var, default]}
    hidden_var_classes = {}   # var => set(class)
    read_points = {}   # key => [file:line]  (成员访问)
    fallback_expr = {}  # key => [(loc, 表达式原文, 字面量或None)]
    setting_keys = {}
    docs_txt = ''
    for dp in glob.glob('docs/zh/reference/*.md'):
        docs_txt += io.open(dp, encoding='utf-8', errors='replace').read()

    for p in sorted(glob.glob(SRC + '/**/*.php', recursive=True)):
        rel = p.replace('src/', '')
        php = io.open(p, encoding='utf-8').read()
        ns = re.search(r'^namespace\s+([^;]+);', php, re.M)
        ns = ns.group(1).strip() if ns else ''
        cls = re.findall(r'^(?:final\s+|abstract\s+)?(?:class|trait)\s+(\w+)', php, re.M)
        full = (ns + '\\' + cls[0]) if cls else ns

        for var in OPT_VARS:
            keys = extract_array(php, var)
            if keys:
                declared.setdefault(full, {}).update({k: var for k in keys})

        for var in HIDDEN_VARS:
            m = re.search(r'\$' + var + r'\s*=\s*\[(.*?)\n\s*\];', php, re.S)
            if not m:
                continue
            hidden_var_classes.setdefault(var, set()).add(full)
            pending = ''
            for line in m.group(1).split('\n'):
                s = line.strip()
                if s.startswith('//'):
                    nm = re.search(r'@used-by\s+([^\s（(]+)', s)
                    if nm:
                        pending = 'external:' + nm.group(1)
                    continue
                if len(line) - len(line.lstrip()) > 8:
                    continue
                mm = re.match(r"'([^']+)'\s*=>\s*(.*)$", s)
                if not mm:
                    continue
                key = mm.group(1)
                val_raw = re.sub(r'\s*//.*$', '', mm.group(2)).strip().rstrip(',')
                note = pending
                nm = re.search(r'@used-by\s+([^\s（(]+)', s)
                if nm:
                    note = 'external:' + nm.group(1)
                pending = ''
                hidden.setdefault(full, {})[key] = [var, php_literal_norm(val_raw), note]

        for i, line in enumerate(php.split('\n'), 1):
            for m in re.finditer(r"(?:->|::_\(\)->)options\s*\[\s*['\"]([a-z0-9_\-]+)['\"]\s*\]", line):
                key = m.group(1)
                loc = '%s:%d' % (rel, i)
                read_points.setdefault(key, []).append(loc)
                fb = re.search(r"options\s*\[\s*['\"][a-z0-9_\-]+['\"]\s*\]\s*\?\?\s*([^;]+)", line)
                if fb:
                    raw = fb.group(1).strip().rstrip(',')
                    norm, shown = parse_fallback(raw)
                    fallback_expr.setdefault(key, []).append((loc, shown if shown else raw.strip(), norm))
            for m in re.finditer(r"(?<!_S)\b_?Setting\(\s*['\"]([a-z0-9_\-]+)['\"]", line):
                setting_keys.setdefault(m.group(1), []).append('%s:%d' % (rel, i))

    declared_keys = set(k for d in declared.values() for k in d)
    hidden_keys = set(k for d in hidden.values() for k in d)
    implicit_keys = set(read_points) - declared_keys - hidden_keys

    def default_of(key):
        for d in hidden.values():
            if key in d:
                return d[key][1]
        return None

    warnings = []
    # W1: 隐藏项与正式选项重复
    for k in sorted(hidden_keys & declared_keys):
        where = [c for c, d in declared.items() if k in d]
        warnings.append('隐藏项与正式选项重复：%s（已在 %s 的正式选项里，建议从隐藏表删掉）' % (k, ', '.join(where)))
    # W2: 隐藏项没有任何读取点（标了 @used-by 外部包的除外 —— 那种是外部读取）
    for k in sorted(hidden_keys):
        if k in read_points:
            continue
        note = ''
        for d in hidden.values():
            if k in d and len(d[k]) > 2:
                note = d[k][2]
        if note.startswith('external:'):
            continue
        warnings.append('隐藏项没有任何读取点：%s（源码里没人读它，疑似历史遗留；'
                        '若由外部包读取，请在隐藏表那行加 `// @used-by 包名`）' % k)
    # W3: 隐藏项默认值与源码兜底不一致（每个键只报一条）
    for k in sorted(hidden_keys):
        hv = default_of(k)
        fbs = fallback_expr.get(k, [])
        if hv is None or not fbs:
            continue
        lits = sorted(set(v for _, _, v in fbs if v is not None))
        if lits and hv not in lits:
            warnings.append('默认值待确认：%s 隐藏表写 %s，源码兜底是 %s（%s，共 %d 处）'
                            % (k, hv, '/'.join(lits), fbs[0][0], len(fbs)))
    # W4: 属性名拼写变体
    for var in sorted(hidden_var_classes):
        if var != 'hidden_options':
            warnings.append('隐藏表属性名疑似拼错：$%s（在 %s）—— 建议统一为 $hidden_options；'
                            '注意 App::__construct() 会清空 $hidden_options，改名要连那条清理语句一起改'
                            % (var, ', '.join(sorted(hidden_var_classes[var]))))
    # W5: 隐藏项未被任何参考文档提及
    for k in sorted(hidden_keys):
        if k not in docs_txt:
            warnings.append('隐藏项未被任何参考文档提及：%s' % k)
    # W6: 隐式选项（谁都没管）
    for k in sorted(implicit_keys):
        warnings.append('隐式选项（既没声明也没进隐藏表）：%s <- %s' % (k, ', '.join(read_points[k][:2])))
    # W7: 隐藏表被运行时代码清空/覆写（改了拼写之后最容易踩：App::__construct() 会清它）
    for p in sorted(glob.glob(SRC + '/**/*.php', recursive=True)):
        rel = p.replace('src/', '')
        for i, line in enumerate(io.open(p, encoding='utf-8').read().split('\n'), 1):
            m = re.search(r'\$this->(hidden_options|hiden_options)\s*=\s*\[\s*\]', line)
            if m and 'protected $' not in line:
                warnings.append('隐藏表会被运行时清空：%s:%d 有 `$this->%s = []`，'
                                '构造/初始化之后读不到隐藏表（要么删这句，要么让文档工具读源码）'
                                % (rel, i, m.group(1)))

    if json_mode:
        print(json.dumps({
            'declared': {c: sorted(d) for c, d in declared.items()},
            'hidden': {c: {k: v for k, v in d.items()} for c, d in hidden.items()},
            'implicit': {k: read_points[k] for k in sorted(implicit_keys)},
            'setting_keys': setting_keys,
            'warnings': warnings,
        }, ensure_ascii=False, indent=2))
        return 1 if warnings else 0

    line = '=' * 78
    print(line)
    print('选项扫描：%d 个类声明了正式选项（%d 键）；隐藏表 %d 个类（%d 键）'
          % (len(declared), len(declared_keys), len(hidden), len(hidden_keys)))
    print(line)

    print('\n## 1) 隐藏选项（$hidden_options / $hiden_options）')
    if not hidden:
        print('  （没有）')
    for c in sorted(hidden):
        var = next(iter(hidden[c].values()))[0] if hidden[c] else '?'
        print('  %s   [属性 $%s]' % (c, var))
        for k in sorted(hidden[c]):
            default = hidden[c][k][1]
            rp = read_points.get(k, [])
            fbs = fallback_expr.get(k, [])
            doc = '有' if k in docs_txt else '**无**'
            extra = hidden[c][k][2] if len(hidden[c][k]) > 2 else ''
            if extra.startswith('external:') and not rp:
                rp = ['由外部包读取：' + extra.split(':', 1)[1]]
            print('    %-40s 默认=%-18s 读取=%-46s 文档=%s'
                  % (k, default if default is not None else '(表达式)',
                     (', '.join(rp[:2]) + ('…' if len(rp) > 2 else '')) if rp else '-', doc))
            if fbs:
                shown = fbs[0][1]
                shown = shown if len(shown) <= 46 else shown[:43] + '...'
                print('    %-40s   ↳ 源码兜底：%s（%s）' % ('', shown, fbs[0][0]))

    print('\n## 2) 隐式选项（读了但既没声明也没进隐藏表）')
    print('  （无）' if not implicit_keys else '')
    for k in sorted(implicit_keys):
        print('  %-40s %s' % (k, ', '.join(read_points[k][:3])))

    print('\n## 3) 设置键（Setting() 读，属另一套机制）')
    for k in sorted(setting_keys):
        print('  %-40s %s' % (k, ', '.join(setting_keys[k][:3])))

    print('\n## 4) 告警（%d 条）' % len(warnings))
    for w in warnings:
        print('  ! ' + w)
    return 1 if warnings else 0


if __name__ == '__main__':
    sys.exit(main())
