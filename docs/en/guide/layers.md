# 2-1 The Four Layers and the Calling Rules

> **This chapter's content has been merged into [Chapter 1-3 Directory Structure and the Four Layers](project-structure.md)** — what remains here is a **signpost page**: Volume 2's chapter numbers don't have to be renumbered (the cheapest option when merging or splitting chapters), and you still land here from any chapter of Volume 2.

The rules of the four layers, the layer-violation matrix, Helper layering, global functions, and common errors **are all in Chapter 1-3**. Only a quick-reference table stays here, for fast lookup:

| Layer | One-line duty | Don't |
|---|---|---|
| System | wiring + shared kernel (**not one of the four layers**) | don't call Business/Model directly, skipping `*Action`; the four layers don't call its wiring actions back either |
| Controller | entry and exit of a request | don't touch Model/Db, don't write business rules |
| Business | business orchestration (**stateless**) | don't read `$_GET`/`$_POST`/`Session`; whatever you need comes in as parameters |
| Model | data access | don't write business decisions, don't throw business exceptions |
| View | display only | don't query the database, don't call Business |

The layer-violation matrix in one sentence: **controllers don't touch Db/Model, Business doesn't touch the request context, views read only and never write**; `Db`/`Session`/`Service`/`View` occupy no row or column of the matrix — Chapter 1-3 states their rules in a few sentences; the cost of a layer violation is not an error but the silent loss of overriding, multi-app, and CLI/test reuse.

- Full content: [Chapter 1-3 Directory Structure and the Four Layers](project-structure.md) (directory conventions, naming rules, the five layers' duties, the violation matrix, violation examples, common errors)
- Directory structure and naming rules are in the same chapter.

## Map of this volume

Volume 2 (Chapters 2-1–2-20) is ordered as "read the timing first → then walk the request path → then add the cross-cutting capabilities → framework mechanics and advanced topics last":

| Stage | Chapters | What it covers |
|---|---|---|
| Rules | [**1-3**](project-structure.md) | what each of the four layers owns, and who may not call whom (content lives in Volume 1; this page is only a signpost) |
| Timing and request path | 2-2–2-8 | [Request lifecycle](lifecycle.md) (including which built-in components the framework installs by default) → [Routing](routing.md) → [Route hooks](route-hooks.md) → [Controllers](controllers.md) → [Views](views.md) → [Database](database.md) → [Model](model.md) |
| Cross-cutting capabilities | 2-9–2-11 | [Helper and global functions](helper.md), [Forms and validation](validator.md), [Session](session.md) |
| Framework mechanics | 2-12–2-13 | [Exceptions](exception.md), [Events](events.md) |
| Advanced | 2-14–2-18 | [Cache](cache.md), [Internationalization](i18n.md), [CLI](cli.md), [Testing](testing.md), [Security and performance](security-performance.md) |
| User / admin systems (usage) | 2-19–2-20 | [Using the user system](user.md), [Using the admin system](admin.md) (to implement your own see [Chapter 4-11](impl-user.md) and [Chapter 4-12](impl-admin.md)) |

## Next steps

- [Chapter 2-2 Request Lifecycle](lifecycle.md): when these layers get wired up.
- [Chapter 2-3 Advanced Routing](routing.md): how a request lands on a controller method.
- [Chapter 2-5 Controllers](controllers.md): how to take input, and the ways to produce output.
- Reference manual: [DuckPhp\Foundation\Controller\ControllerHelper](../reference/Foundation-Controller-ControllerHelper.md), [DuckPhp\Foundation\Business\BusinessHelper](../reference/Foundation-Business-BusinessHelper.md), and the four-layer base classes [Controller\Base](../reference/Foundation-Controller-Base.md) / [Business\Base](../reference/Foundation-Business-Base.md) / [Model\Base](../reference/Foundation-Model-Base.md).
