-- RouteHookWebInstaller test schema (sqlite)
CREATE TABLE IF NOT EXISTS install_demo (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(64) NOT NULL
);
INSERT INTO install_demo (name) VALUES ('demo');
