<?php
/**
 * DuckPhp
 * From this time, you never be alone~
 *
 * Built-in view for RouteHookWebInstaller.
 * Included by RouteHookWebInstaller::show() after extract($data).
 * Use brace style for PHP control structures; HTML keeps its own indentation.
 */

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?=__h($title)?></title>
<style>
body { font-family: sans-serif; max-width: 720px; margin: 2em auto; color: #222; }
table { border-collapse: collapse; width: 100%; }
td, th { border: 1px solid #ccc; padding: 4px 8px; text-align: left; }
.ok { color: #0a0; }
.fail { color: #a00; }
.error { color: #a00; }
input, select, button { padding: 4px 8px; }
fieldset { border: 1px solid #ccc; margin: 1em 0; padding: 0 1em 1em; }
legend { font-weight: bold; }
.hint { font-size: 0.85em; color: #666; }
</style>
</head>
<body>
<h1>DuckPhp Web Installer</h1>
<?php if (!empty($installed)) { ?>
<h2>Already Installed</h2>
<p>The application is already installed. To reinstall, please remove the <code>installed</code> entry from the ext options data file.</p>
<?php } else { ?>
<form method="post">
    <fieldset>
        <legend>Environment Check</legend>
        <p>Current controller_resource_prefix: <code><?=__h((string)($controller_resource_prefix ?? ''))?></code></p>
        <table>
            <thead>
                <tr><th>Item</th><th>Status</th></tr>
            </thead>
            <tbody>
<?php foreach ($checks ?? [] as $item) { ?>
                <tr>
                    <td><?=__h((string)$item[1])?></td>
                    <td class="<?=$item[0] ? 'ok' : 'fail'?>"><?=$item[0] ? 'OK' : 'FAIL'?></td>
                </tr>
<?php } ?>
            </tbody>
        </table>
    </fieldset>
<?php if (!empty($use_redis)) { ?>
    <fieldset>
        <legend>Redis Config</legend>
<?php if (!empty($redis_error_message)) { ?>
        <p class="error"><?=__h((string)$redis_error_message)?></p>
<?php } ?>
        <p><label>
            <input type="checkbox" name="redis_follow_root" value="1"<?= empty($redis_can_follow_root) ? '' : ' checked' ?> data-target="redis-config"<?= empty($redis_can_follow_root) ? ' disabled' : '' ?>> Follow Main Application
<?php if (empty($redis_can_follow_root)) { ?>
            <span class="hint">(root app has no redis configured)</span>
<?php } ?>
        </label></p>
        <div id="redis-config">
            <p><label>Host: <input type="text" name="redis[host]" value="<?=__h((string)($post['redis']['host'] ?? '127.0.0.1'))?>"></label></p>
            <p><label>Port: <input type="text" name="redis[port]" value="<?=__h((string)($post['redis']['port'] ?? '6379'))?>"></label></p>
            <p><label>Auth: <input type="password" name="redis[auth]" value="<?=__h((string)($post['redis']['auth'] ?? ''))?>"></label></p>
            <p><label>Select: <input type="text" name="redis[select]" value="<?=__h((string)($post['redis']['select'] ?? '0'))?>"></label></p>
            <p class="hint">Multiple redis configs are supported: add more entries to the config file manually after install.</p>
        </div>
    </fieldset>
<?php } ?>
<?php if (!empty($use_database)) { ?>
    <fieldset>
        <legend>Database Config</legend>
<?php if (!empty($database_error_message)) { ?>
        <p class="error"><?=__h((string)$database_error_message)?></p>
<?php } ?>
        <p><label>
            <input type="checkbox" name="database_follow_root" value="1"<?= empty($database_can_follow_root) ? '' : ' checked' ?> data-target="database-config"<?= empty($database_can_follow_root) ? ' disabled' : '' ?>> Follow Main Application
<?php if (empty($database_can_follow_root)) { ?>
            <span class="hint">(root app has no database configured)</span>
<?php } ?>
        </label></p>
        <div id="database-config">
            <p><label>Driver: <select name="driver" onchange="toggleDatabaseDriver(this)">
<?php $dc_driver = (string) ($post['driver'] ?? ''); ?>
<?php foreach ($drivers as $driver) { ?>
                <option value="<?=__h($driver)?>"<?= $driver === $dc_driver ? ' selected' : '' ?>><?=__h($driver)?></option>
<?php } ?>
            </select></label></p>
            <p data-db-file><label>File: <input type="text" name="database[file]" value="<?=__h((string)($post['database']['file'] ?? 'database/database.db'))?>"></label></p>
            <p data-db-server><label>Host: <input type="text" name="database[host]" value="<?=__h((string)($post['database']['host'] ?? '127.0.0.1'))?>"></label></p>
            <p data-db-server><label>Port: <input type="text" name="database[port]" value="<?=__h((string)($post['database']['port'] ?? ''))?>"></label></p>
            <p data-db-server><label>Database: <input type="text" name="database[dbname]" value="<?=__h((string)($post['database']['dbname'] ?? ''))?>"></label></p>
            <p data-db-server><label>Username: <input type="text" name="database[username]" value="<?=__h((string)($post['database']['username'] ?? ''))?>"></label></p>
            <p data-db-server><label>Password: <input type="password" name="database[password]" value="<?=__h((string)($post['database']['password'] ?? ''))?>"></label></p>
            <p class="hint">Multiple database configs (master/slave) are supported: add more entries to the config file manually after install.</p>
        </div>
        <hr/>
        <p><label><input type="checkbox" name="force" value="1"> Force reinstall (drop existing tables)</label></p>
    </fieldset>
<?php } ?>
<?php if (!empty($custom_error_message)) { ?>
    <p class="error"><?=__h((string)$custom_error_message)?></p>
<?php } ?>
<?php if (!empty($custom_html)) { ?>
    <fieldset>
        <legend>Customer Setting</legend>
<?=$custom_html?>
    </fieldset>
<?php } ?>
    <input type="hidden" name="action" value="install">
    <p><button type="submit">Install</button></p>
</form>
<script>
function toggleRows(rows, show) {
    for (var i = 0; i < rows.length; i++) {
        rows[i].style.display = show ? '' : 'none';
    }
}
function toggleFollowRoot(cb) {
    var el = document.getElementById(cb.getAttribute('data-target'));
    if (el) { el.style.display = cb.checked ? 'none' : ''; }
}
function toggleDatabaseDriver(sel) {
    var file = (sel.value === 'sqlite' || sel.value === 'duckdb');
    toggleRows(document.querySelectorAll('[data-db-file]'), file);
    toggleRows(document.querySelectorAll('[data-db-server]'), !file);
}
var cbs = document.querySelectorAll('input[type="checkbox"][data-target]');
for (var i = 0; i < cbs.length; i++) {
    toggleFollowRoot(cbs[i]);
    cbs[i].onchange = function() { toggleFollowRoot(this); };
}
var __driver = document.querySelector('[name="driver"]');
if (__driver) { toggleDatabaseDriver(__driver); }
</script>
<?php } ?>
</body>
</html>
