<?php
if (php_sapi_name() === "cli") {
    die('Currently CLI not supported');
}
$config['base_path'] = str_replace('/', DIRECTORY_SEPARATOR, str_replace('/' . basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_FILENAME']));
$config['app_path'] = str_replace('/', DIRECTORY_SEPARATOR, str_replace('/' . basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_FILENAME']) . '/' . 'app');
$config['lib_path'] = str_replace('/', DIRECTORY_SEPARATOR, str_replace('/' . basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_FILENAME']) . '/' . 'core');
if (php_sapi_name() !== "cli") {
    $config['base_url'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/phpcore';
}

$config['time_zone'] = 'Asia/Kolkata';
$config['debug'] = TRUE;
$config['script_path'] = '/';
$config['database'] = FALSE;
$config['session'] = FALSE;

$config['log_threshold'] = [1, 5];
$config['log_path'] = '';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';
