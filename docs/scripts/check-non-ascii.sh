#!/bin/bash
# 扫描 src/ 下所有 PHP 文件中的非 ASCII 字符（注释/字符串等）
cd "$(dirname "$0")/../.." || exit 1
grep -rnP '[^\x00-\x7F]' src/ --include='*.php'
echo "---"
echo "Total non-ASCII lines: $(grep -rnP '[^\x00-\x7F]' src/ --include='*.php' | wc -l)"
