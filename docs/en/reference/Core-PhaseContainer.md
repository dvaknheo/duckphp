# DuckPhp\Core\PhaseContainer

## Introduction

`PhaseContainer` is DuckPHP's "phase container", and the container that actually owns instances behind `SingletonExTrait::_()`.

It stores object instances in several parallel spaces, keyed by "container name (phase) + class name":

- `containers`: one big table of `container name => (class name => instance)`;
- `current`: the currently active phase/container name;
- `default`: the default container name where shared instances live (usually `#shared` in the framework);
- `shared_classes`: the list of classes marked as "shareable across phases" (see below).

When an instance of a class is requested (`Class::_()`), `_GetObject()` looks in this order: **the instance in the current phase → if the class is in `shared_classes`, the shared instance in the default container → if neither is found, automatically `new` one and register it into the target container**. This builds the multi-instance layout of "root app and child apps independent from each other, with a few shareable components".

DuckPHP's root phase name is usually `''` (empty string); the default shared container name is recorded as `#shared` in `KernelTrait`. A child app's phase name looks like `parentPhase:namespace/name`.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class PhaseContainer`
- Key properties: `public static $instance`; instance properties `$containers`, `$current`, `$default`, `$shared_classes`.

## Usage

Used internally by `KernelTrait`/components; business code normally only touches the `_()` and `GetObject()` wrappers. You can still drive it manually:

```php
use DuckPhp\Core\PhaseContainer;

// set the "current container" manually
$c = PhaseContainer::_();
$c->setCurrentContainer('myapp');

// attach/query shared classes
$c->addSharedClasses([MyShared::class => true]);

// get an object (created at the current location if absent; shared classes are found or built in default)
$obj = PhaseContainer::GetObject(MyShared::class);

// get an object from a specific phase container
$other = $c->getClassOfContainer(MyShared::class, 'phase-name');
```

Tests/isolation usually reset like this:

```php
PhaseContainer::RestAllContainerForTesting(); // replace the static $instance with a fresh container
```

## Caveats

- Instances in the default container (`#shared`) are globally shared; a class to be shared must be declared via `addSharedClasses($classes)`, otherwise `_GetObject()` will not fall back to the default container.
- A manual `_(?object $object)` only replaces the **static `$instance` (the container itself)**; to register "an instance of some class", call `GetObject($class, $object)` / `createLocalObject` on the passed object.
- `createLocalObject` / `removeLocalObject` always act on `current`, i.e. they keep the instance bound to the current phase (local).
- `dumpAllObject()` / `Dump()` are debug-only views of the container layout (the printout marks shared classes with `*`).

## Methods

### Public static methods

    public static function _ (?object $object = null)
Returns the single container instance; with an object passed, sets the static $instance to it and returns it (handy for replacing the whole container in tests).

    public static function GetObject(string $class, ?object $object = null)
Convenience getter: delegates to `_()->_GetObject($class,$object)`; a non-null `$object` registers/replaces the corresponding instance.

    public static function RestAllContainerForTesting()
Replaces the static $instance with a fresh container — generally only for tests starting from a clean state.

    public static function Dump()
Dumps the full container state (current/default/shared_classes/containers) to stdout for debugging.

### Instance methods

    public function _GetObject(string $class, ?object $object = null): object
Core lookup + creation: looks in the current container first; if the class is in shared_classes, then in default; otherwise news/registers the instance (in the target container).

    public function setDefaultContainer($class)
Sets the default (shared) container name — where shared instances go.

    public function addSharedClasses($classes)
Marks classes as shared (class names as keys, truthy values).
Example `addSharedClasses([Some::class => true])`.

    public function removeSharedClasses($classes)
Removes these classes from the shared list (value is an array list).

    public function setCurrentContainer($container)
Sets the current phase/container name.

    public function getCurrentContainer()
Returns the current container name.

    public function issetContainer($phase)
Whether a container (phase) already exists (has objects built).

    public function createLocalObject($class, $object = null)
Creates (or places the given object) in the current container and registers it, returning the object; the instance sticks to the current phase and never consults the default container.

    public function removeLocalObject($class)
Removes that class's instance from the current container.

    public function getClassOfContainer($class, $phase = '')
Gets a class's instance in a given container (no lookup, no creation; null if absent).

    public function dumpAllObject()
Full dump (see Dump).

### Protected methods

    protected function createObject(string $class): object
`new`s an instance of the given class (subclasses may replace how instances are built).

    protected function createObjectToContainer($container_name, $class, $object)
Registers and returns the instance in the given container (createObject first when the object is null).

    protected function getObjectInContainer($container_name, $class, $object)
Returns by "instance present or not" in a container; a passed $object is registered over it in that container.

## Related links

- [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) — the `_()` singleton entry implemented on top of this container
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — sets the root phase (`''`) and the shared container name (`#shared`), and wires up initContainer/addSharedClasses
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the main class touching the container
