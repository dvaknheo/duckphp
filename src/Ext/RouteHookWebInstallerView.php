<?php
/**
 * DuckPhp
 * From this time, you never be alone~
 *
 * Built-in view for RouteHookWebInstaller.
 * Included by RouteHookWebInstaller::show() after extract($data).
 * Use brace style for PHP control structures; HTML keeps its own indentation.
 *
 * i18n: all UI texts are translated via __hl('webinstaller.*') language keys.
 * - Default (English) sentences are provided by RouteHookWebInstaller::builtin_default_sentences
 *   and imported into Lang via importDefaultSentences() at init().
 * - Override built-in defaults with the option 'web_installer_default_sentences'.
 * - Provide real translations per language via 'lang_simple_mode_only_sentences'
 *   or config/lang/{language}.php (real translations always win over defaults).
 */

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?=__h($title ?? '')?></title>
<?php if (!empty($installed)) { ?>
<meta http-equiv="refresh" content="5;url=<?=__h(__url(''))?>">
<?php } ?>
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
<h1><?=__hl('webinstaller.h1')?></h1>
<?php if (!empty($installed)) { ?>
<h2><?=__hl('webinstaller.install_complete')?></h2>
<p><?=__hl('webinstaller.congratulations')?></p>
<p><?=__hl('webinstaller.install_redirect')?> <a href="<?=__h(__url(''))?>"><?=__hl('webinstaller.manual_redirect')?></a></p>
<?php } else { ?>
<form method="post">
    <fieldset>
        <legend><?=__hl('webinstaller.env_check')?></legend>
        <p><?=__hl('webinstaller.current_controller_prefix')?>: <code><?=__h((string)($controller_resource_prefix ?? ''))?></code></p>
        <table>
            <thead>
                <tr><th><?=__hl('webinstaller.item')?></th><th><?=__hl('webinstaller.status')?></th></tr>
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
        <legend><?=__hl('webinstaller.redis_config')?></legend>
<?php if (!empty($redis_error_message)) { ?>
        <p class="error"><?=__h((string)$redis_error_message)?></p>
<?php } ?>
        <p><label>
            <input type="checkbox" name="redis_follow_root" value="1"<?= empty($redis_can_follow_root) ? '' : ' checked' ?> data-target="redis-config"<?= empty($redis_can_follow_root) ? ' disabled' : '' ?>> <?=__hl('webinstaller.follow_main_application')?>
<?php if (empty($redis_can_follow_root)) { ?>
            <span class="hint">(<?=__hl('webinstaller.no_redis_in_root')?>)</span>
<?php } ?>
        </label></p>
        <div id="redis-config">
            <p><label><?=__hl('webinstaller.host')?>: <input type="text" name="redis[host]" value="<?=__h((string)($post['redis']['host'] ?? '127.0.0.1'))?>"></label></p>
            <p><label><?=__hl('webinstaller.port')?>: <input type="text" name="redis[port]" value="<?=__h((string)($post['redis']['port'] ?? '6379'))?>"></label></p>
            <p><label><?=__hl('webinstaller.auth')?>: <input type="password" name="redis[auth]" value="<?=__h((string)($post['redis']['auth'] ?? ''))?>"></label></p>
            <p><label><?=__hl('webinstaller.select')?>: <input type="text" name="redis[select]" value="<?=__h((string)($post['redis']['select'] ?? '0'))?>"></label></p>
            <p class="hint"><?=__hl('webinstaller.multi_redis_hint')?></p>
        </div>
    </fieldset>
<?php } ?>
<?php if (!empty($use_database)) { ?>
    <fieldset>
        <legend><?=__hl('webinstaller.database_config')?></legend>
<?php if (!empty($database_error_message)) { ?>
        <p class="error"><?=__h((string)$database_error_message)?></p>
<?php } ?>
        <p><label>
            <input type="checkbox" name="database_follow_root" value="1"<?= empty($database_can_follow_root) ? '' : ' checked' ?> data-target="database-config"<?= empty($database_can_follow_root) ? ' disabled' : '' ?>> <?=__hl('webinstaller.follow_main_application')?>
<?php if (empty($database_can_follow_root)) { ?>
            <span class="hint">(<?=__hl('webinstaller.no_database_in_root')?>)</span>
<?php } ?>
        </label></p>
        <div id="database-config">
            <p><label><?=__hl('webinstaller.driver')?>: <select name="driver" onchange="toggleDatabaseDriver(this)">
<?php $dc_driver = (string) ($post['driver'] ?? ''); ?>
<?php foreach ($drivers ?? [] as $driver) { ?>
                <option value="<?=__h($driver)?>"<?= $driver === $dc_driver ? ' selected' : '' ?>><?=__h($driver)?></option>
<?php } ?>
            </select></label></p>
            <p data-db-file><label><?=__hl('webinstaller.file')?>: <input type="text" name="database[file]" value="<?=__h((string)($post['database']['file'] ?? 'database/database.db'))?>"></label></p>
            <p data-db-server><label><?=__hl('webinstaller.host')?>: <input type="text" name="database[host]" value="<?=__h((string)($post['database']['host'] ?? '127.0.0.1'))?>"></label></p>
            <p data-db-server><label><?=__hl('webinstaller.port')?>: <input type="text" name="database[port]" value="<?=__h((string)($post['database']['port'] ?? ''))?>"></label></p>
            <p data-db-server><label><?=__hl('webinstaller.dbname')?>: <input type="text" name="database[dbname]" value="<?=__h((string)($post['database']['dbname'] ?? ''))?>"></label></p>
            <p data-db-server><label><?=__hl('webinstaller.username')?>: <input type="text" name="database[username]" value="<?=__h((string)($post['database']['username'] ?? ''))?>"></label></p>
            <p data-db-server><label><?=__hl('webinstaller.password')?>: <input type="password" name="database[password]" value="<?=__h((string)($post['database']['password'] ?? ''))?>"></label></p>
            <p class="hint"><?=__hl('webinstaller.multi_db_hint')?></p>
        </div>
        <hr/>
        <p><label><input type="checkbox" name="force" value="1"> <?=__hl('webinstaller.force_reinstall')?></label></p>
    </fieldset>
<?php } ?>
<?php if (!empty($custom_error_message)) { ?>
    <p class="error"><?=__h((string)$custom_error_message)?></p>
<?php } ?>
<?php if (!empty($custom_html)) { ?>
    <fieldset>
        <legend><?=__hl('webinstaller.customer_setting')?></legend>
<?=$custom_html?>
    </fieldset>
<?php } ?>
    <input type="hidden" name="action" value="install">
    <p><button type="submit"><?=__hl('webinstaller.install')?></button></p>
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
