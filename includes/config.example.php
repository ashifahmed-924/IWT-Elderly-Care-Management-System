<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'eldercare_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'ElderCare');
define('BASE_PATH', dirname(__DIR__));

// Auto-detect base path so CSS/JS work in subfolders like /eldercare/public/
if (!defined('PUBLIC_URL')) {
    if (php_sapi_name() === 'cli') {
        define('PUBLIC_URL', '/eldercare/public/');
    } else {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (preg_match('#^(.*?/public)/#', $script, $m)) {
            define('PUBLIC_URL', $m[1] . '/');
        } else {
            define('PUBLIC_URL', rtrim(dirname($script), '/') . '/');
        }
    }
}
