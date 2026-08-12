-- RouteHookWebInstaller test schema (sqlite): create tables
CREATE TABLE IF NOT EXISTS {prefix}install_demo (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(64) NOT NULL
);
