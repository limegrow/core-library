<?php
// phpcs:ignoreFile -- this is test

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestConnector.php';

// phpcs:disable
if (getenv('PSPID') && getenv('PSPID') !== 'xxxxx') {
    define('PSPID', getenv('PSPID'));
    define('SIGNATURE', getenv('SIGNATURE'));
    define('USER', getenv('USER'));
    define('PASSWORD', getenv('PASSWORD'));
    define('PASSPHRASE', getenv('PASSWORD'));

    // phpcs:enable
} else {
    // Load config
    if (file_exists(__DIR__ . '/config.local.ini')) {
        $config = parse_ini_file(__DIR__ . '/config.local.ini', true);
    } else {
        $config = parse_ini_file(__DIR__ . '/config.ini', true);
    }

    define('PSPID', $config['pspid']);
    define('SIGNATURE', $config['signature']);
    define('USER', $config['USER']);
    define('PASSWORD', $config['PASSWORD']);
    define('PASSPHRASE', $config['PASSWORD']);
}
