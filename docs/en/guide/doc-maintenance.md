# 4-8 Documentation and Reference Manual Maintenance

> What this solves: after you change the source code, how to keep `docs/zh/reference/` (110 per-class pages + 4 summary pages) and `docs/zh/guide/` (this guide) from turning into lies; and which automated gates this repository has built for that.
> Prerequisites: [Chapter 4-7 Test Infrastructure and the Coverage Pipeline](coverage.md). About 20 minutes.
> The two authoritative manuals (this chapter is their "guided tour"; they win on details):
> [the reference-manual maintenance guide](../reference-maintenance-guide.md), [the user-guide maintenance guide](../guide-maintenance-guide.md).

## Minimal example

After changing one class's source, the minimal self-check set:

```bash
# run all commands below from the repository root
wsl -e bash -lc "python3 docs/scripts/check-doc-links.py docs/zh"   # expect broken: 0
wsl -e bash -lc "bash docs/scripts/check-non-ascii.sh"              # run after touching src/; expect 0
# drift scan: save drift.py into a temp directory and run it (the script is in the maintenance guide §5)
python3 <tmp>/drift.py --all
```

Then take one more look at `docs/zh/reference/<the page>.md`: **are the method table and the option table consistent with the source**.

## How it works

### 1. The three-layer structure of the docs (do not write into the wrong layer)

```
docs/zh/index.md            ← 指路页：只告诉你"去哪找"，不复制目录  # signpost page: only tells you "where to look", does not duplicate the table of contents
docs/zh/guide/              ← 用户指南：一页总目录 + 四卷 47 章 + 4 附录（"怎么做"）  # the user guide: one table-of-contents page + four volumes, 47 chapters + 4 appendices ("how to do it")
docs/zh/reference/          ← 参考手册：一类一页 + 汇总页（"有什么"）  # the reference manual: one page per class + summary pages ("what exists")
docs/zh/*maintenance-guide* ← 两份维护指南（"怎么维护"）  # the two maintenance guides ("how to maintain")
skeleton/AGENTS.md          ← 工程内约定（随包、随生成工程走）：目录树 / 命名 / 分层规则  # in-project conventions (shipped with the package and generated projects): directory tree / naming / layering rules
```

| Content | Where it belongs |
|---|---|
| "What is this method's signature / this option's default" | `reference/` (per-class pages + the `options*.md` summaries) |
| "I want to do X — how do I write it" | `guide/` (chapters; link to the reference page instead of copying the signature table) |
| "What pitfalls does this kind of change have" / "what is the current state" | the two `*-maintenance-guide.md` |
| "Which files exist in this project, what to create, what is forbidden" | `skeleton/AGENTS.md` (the **single** file-level inventory and project conventions; shipped to users with the package, so it talks about the project only and does not duplicate framework APIs) |
| "What changed this time, and why" | the commit message (**do not write it into the docs**) |

**The key "single source of truth" principle**: option defaults and method signatures are maintained only on the reference pages; the guide uses them without restating them. Otherwise one source change forces two doc edits, and drift is guaranteed.

### 2. The consistency gate: the drift scan

> **Tool scripts live under `docs/scripts/` and are committed together with the docs**: they serve only documentation generation and verification (not part of the framework runtime); keeping them in `docs/` makes "documentation change + generator change" one commit. The commands in this chapter and in both maintenance guides run from **the repository root**.

The per-class pages of `reference/` and the source are reconciled by **set comparison** (the maintenance guide §5 gives the full script; save it as `%TEMP%\drift.py` and run it):

- `missing-method`: in the source but not in the docs → **must be added**;
- `missing-option`: a key in `$options` not documented → **must be added**;
- `extra-option`: documented but not in the source (a dead option / rename leftover) → **must be deleted or renamed**;
- `extra-method`: a custom method in a doc's "usage" example → usually nothing to do.

The repository's current state is **0 inconsistencies** (all `missing-*` empty).

> ⚠️ `docs/scripts/gen-reference.php verify` **misses methods** in big files that `use Trait { … as … }` and override, and then falsely reports "extra methods". Judge consistency by the drift scan.

### 3. The sync checklist for add/rename/option changes

**Adding a class**:

1. Write `docs/zh/reference/<Ns>-<ClassName>.md` from the template (H1 = fully qualified class name; intro / class info / option table / usage / caveats / method list);
2. Register one line in `docs/zh/reference/index.md`;
3. If it belongs to some topic, add a "how to use it" sentence in the corresponding guide chapter and link over;
4. If options were added, run `docs/scripts/gen-options-docs.php` or hand-edit `options.md` / `options-by-class.md` / `options-index.md`.

**Renaming a public name (method name / option key / parameter name)**:

```bash
grep -rn "<旧名>" src tests docs | wc -l      # list them all first; after renaming, grep again = 0
```

- Covers `src/` + `tests/` + `docs/zh/reference/` + `docs/zh/guide/`;
- **Exceptions**: `docs/old/`, `docs/en/`, `README*.md` are archived/stale copies and are not synced with the Chinese docs by default; exclude them from batch replacements, and do not "helpfully align" them either.
- `src/` must not contain Chinese/full-width characters — the most common source is copy-pasting from the Chinese docs (full-width arrows `→`, full-width parentheses). After the change, run `docs/scripts/check-non-ascii.sh` and expect `Total non-ASCII lines: 0`.

**Renumbering/reordering chapters** (stepped into when this guide's Volume 2 was reshuffled): cross-references must be rewritten with **single-pass replacement + a callback map** (Python `re.subn`); sequential `sed` replacement chains into false edits (after `14→8`, `8→17` rewrites it again). Afterwards, use a "filename → chapter number" table to reverse-check that every "Chapter N + link" pair is consistent.

### 4. In-site links: 0 broken links is a hard requirement

```bash
python3 docs/scripts/check-doc-links.py docs/zh     # expect checked N, broken: 0
python3 docs/scripts/check-doc-links.py docs        # the whole repo (docs/old, docs/en have historical broken links, known)
```

The convention: **no link to a page that is not written yet** — use "plain text + `⏳`" in the table of contents and swap in the link once written, so the link check stays at zero broken links forever. **All 47 chapters + 4 appendices of the guide and all 114 reference pages are now written**, so `⏳` appears only when a **newly added** chapter/page is not finished yet (a full-book grep should find only this convention's own text).

### 5. Things not to commit

| Path | Note |
|---|---|
| `docs/zh/guide/.obsidian/`, `docs/zh/reference/.obsidian/` | Local Obsidian configuration (if one slips in: `git rm -r --cached` + `amend`) |
| `tests/data_for_tests/ZAllDemoTest-<length>.txt`, `*/log_*.log`, `test_reports/`, `test_coveragedumps/` | Test artifacts |
| `demo/runtime/*.sqlite`, runtime files under `runtime/` | Runtime artifacts |

Double-check with `git status --short` before committing (do not `git add .`).

### 6. The manuals themselves need maintenance too

- `docs/zh/reference-maintenance-guide.md`: the reference-manual side's "how to" + tools + pitfall table + current state;
- `docs/zh/guide-maintenance-guide.md`: the user-guide side's templates, constraints, check commands, pitfall table, and chapter renumbering recipes.

The discipline: **the manuals keep only "how it is done now" and "the current state"** — the "current state" section is one or two concluding sentences, and the todo list holds only what is genuinely unfinished; **the process of a change, per-file inventories, and debugging stories belong in commit messages** (`git log` is the history) — do not pile journals into the manuals. Lessons with long-term value go into the pitfall table; rules go into the hard constraints.

## Common patterns

**① Only one method's visibility changed**

Change the visibility word of the method entry on the reference page + add a caveat sentence in the guide chapter if usage is affected; run the drift scan to confirm no `missing-method`.

**② Adding an option**

Add a row (key / default / description) to the reference page's "options" table, then run `docs/scripts/gen-options-docs.php` to update the summary pages; if the option will be used in some guide chapter, add a sentence there.

**③ Found an option in the source that is "read but not declared"**

Run `docs/scripts/scan-options.py` and read the warnings. Two dispositions: either declare it formally in `$options`, or list it in `$hidden_options` and write the read site as `?? <default>` (and think through "can the user still set it" — see the `$hidden_options` comments in [`DuckPhp`](../reference/DuckPhp.md)/[`App`](../reference/Core-App.md)).

**④ Found a doc link pointing at a nonexistent page**

Locate it with `check-doc-links.py` first: if the target is not written yet, change it to **plain text + `⏳`** and swap the link back once written; if the page should exist but the path is wrong, just fix the path (the convention is in §4 of this section).

**⑤ The pre-commit documentation self-check trio**

```bash
python3 docs/scripts/check-doc-links.py docs/zh      # links: expect broken: 0
bash docs/scripts/check-non-ascii.sh                 # after touching src/: expect Total non-ASCII lines: 0
python3 - <<'PY'                                # docs UTF-8 check
import pathlib
bad = []
for p in pathlib.Path('docs/zh').rglob('*.md'):
    if '.obsidian' in p.parts:
        continue
    try:
        p.read_bytes().decode('utf-8')
    except UnicodeDecodeError:
        bad.append(str(p))
print('non-utf8:', bad if bad else 'none')
PY
```

(The three core metrics: **0 broken in-site links, all docs UTF-8, `src/` pure ASCII**; see the maintenance guides for the more complete ready-made scripts.)

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| Reference page and source are inconsistent, and nobody noticed | Only the source was changed, not the docs | Run the drift scan (§2) and clear `missing-*` to zero |
| The docs keep a **dead option** | The option was renamed/deleted and the docs did not follow | That is what the drift scan's `extra-option` is; delete it or switch to the new key name |
| A `sed` batch rename broke other sentences | Sequential replacement (after `14→8`, `8→17` rewrites it again) | Single-pass replacement + callback map (`re.subn`); grep to double-check afterwards |
| The link check reports broken links | Pointing at a chapter/page not yet written | Write unfinished ones as "plain text + `⏳`" and swap in the link once done; fix wrong paths directly (§4) |
| Chinese/full-width characters appear in `src/` | Pasted from the Chinese docs | `docs/scripts/check-non-ascii.sh` is the fallback; change back to ASCII |
| `.obsidian/` or test artifacts slip into a commit | `git add .` | Double-check with `git status --short` before committing (§5) |
| The maintenance guides grow ever longer and successors cannot read them | "How this change was made" was written into the body | Keep only rules + pitfalls + current state; the process goes into commit messages |
| `gen-reference.php verify` reports "extra methods" and you delete accordingly | The script misses methods in big files, and only accepts method entries starting with a backtick (this repo uses 4-space indentation) | Judge by the drift scan; treat `verify` as reference only |

## Next steps

- [Chapter 4-9 Performance Tuning and Troubleshooting Handbook](troubleshooting.md): symptom → troubleshooting path.
- [Chapter 4-10 Design Trade-offs and Known Pitfalls](design-notes.md): which behaviors are deliberate and which are pitfalls.
- Maintenance guides: [the reference-manual maintenance guide](../reference-maintenance-guide.md), [the user-guide maintenance guide](../guide-maintenance-guide.md).
