<?php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/constants.php';
require_once __DIR__ . '/core/functions.php';

(function () {
    $uri = uriDecoder();
    loadPage($uri['script_path']);
})();
