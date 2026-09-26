# DuckPhp\HttpServer\HttpServerInterface

## Introduction

`HttpServerInterface` is the contract for DuckPHP's built-in HTTP server launcher: an implementation must provide quick start (`RunQuickly`), running (`run()`), reading the PID (`getPid()`) and shutting down (`close()`). The framework's `HttpServer` implements this interface.

## Class info

- Namespace: `DuckPhp\HttpServer`
- Declaration: `interface HttpServerInterface`

## Usage

Normally you just call `HttpServer::RunQuickly($options)`; to supply your own launcher, implement this interface and keep the "`RunQuickly($options)` starts it in one call" usage identical.

## Methods

### Public methods

    public static function RunQuickly($options)
Quick start: initialise from `$options` and start running (the convention is that the server is up, or has been handed to a system command, once this returns).

    public function run()
Starts/runs the server's main flow.

    public function getPid()
Reads the PID of the background process.

    public function close()
Stops/shuts down the server process.

## Related links

- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) — the default implementation of this interface
