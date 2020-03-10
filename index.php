<?php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/constants.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/logger.php';

require_once __DIR__ . '/app/includes/functions.php';

(function () {
    $uri = uriDecoder();
    loadPage($uri['script_path']);
})();
