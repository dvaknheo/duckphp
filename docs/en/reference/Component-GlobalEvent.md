# DuckPhp\Component\GlobalEvent

A minimal global event bus: `on/fire`; event callbacks can be bound to a specific Phase (the phase is switched temporarily while the callback fires).

## Introduction

`GlobalEvent extends ComponentBase` provides a small mechanism for firing events across components:

- `on($event,$callback)`: registers an event callback under the current Phase;
- `globalOn($event, $phase, $callback)`: explicitly binds a callback to a Phase;
- `fire($event,...$args)`: calls callbacks in registration order (each callback first switches to its bound Phase, then runs);
- `remove($event,?,?)` and `all()` for querying/removal/inspection.

No async or priority ordering: simple sequential execution. Enabled by DuckPhp wiring up `GlobalEvent` with `EXT` (if needed) (events are provided by Engine by default).

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class GlobalEvent extends ComponentBase`

## Usage

```php
use DuckPhp\Component\GlobalEvent;

GlobalEvent::_()->on('user.login_after', function ($u) { /* … */ });
GlobalEvent::_()->globalOn('site.visit', 'admin', function () { /* in admin phase */ });
GlobalEvent::_()->fire('user.login_after', $user);
```

When business code wants this in many places it goes through the Helper layer's Fire/On (HelperAppTrait/Business/… provide FireGlobalEvent/OnGlobalEvent semantics that delegate to it).

## Caveats

- Internally `on` uses the current `App::Phase()` as the bound phase? Actually on()=globalOn(event,App::Phase(),cb), and when fire is called, whatever the current phase is, it switches back to the bound phase.
- When remove is given phase/callback, entries are kept only when both differ; i.e. it only deletes entries completely different from the given phase (or callback)? — note the filtering logic: an entry is kept if and only if calling_phase != phase (and calling_callback != callback). When both are given, whether both must differ to count as deletion has to be read from the implementation. As implemented, an entry is kept when both differ.
- all() returns the internal registry for debugging.

## Methods

### Public methods

    public function on($event, $callback)
Registers a callback under the current Phase (delegates to globalOn(App::Phase(),…)).

    public function globalOn($event, ?string $phase, callable $callback)
(Does not re-add if the same pair already exists for the event) records [phase,cb].

    public function fire($event, ...$args)
If present, iterates: first `App::Phase(bound phase)`, calls the callback, restores the previous phase.

    public function all()
Returns the whole registration map (events).

    public function remove($event, ?string $phase = null, $callback = null)
Deletion: when phase and callback are both absent, unsets the whole event; otherwise removes the non-matching ones (i.e. deletes the matching).

## Related links

- DuckPhp\DuckPhp registers this component when global events are used
- The Fire(&On) shell of AppHelper/…: see Foundation/Helper
