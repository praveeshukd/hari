<?php

require __DIR__.'/../vendor/autoload.php';

$appEnv = __DIR__.'/../.env';
if (is_file($appEnv) && class_exists(Dotenv\Dotenv::class)) {
    Dotenv\Dotenv::createImmutable(dirname($appEnv))->safeLoad();
}

// Use a dedicated MySQL database for feature tests (pdo_sqlite is unavailable here).
putenv('APP_ENV=testing');
putenv('DB_DATABASE=qc_testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';
$_ENV['DB_DATABASE'] = 'qc_testing';
$_SERVER['DB_DATABASE'] = 'qc_testing';
$_ENV['CACHE_STORE'] = 'array';
$_SERVER['CACHE_STORE'] = 'array';
$_ENV['SESSION_DRIVER'] = 'array';
$_SERVER['SESSION_DRIVER'] = 'array';
$_ENV['QUEUE_CONNECTION'] = 'sync';
$_SERVER['QUEUE_CONNECTION'] = 'sync';
$_ENV['MAIL_MAILER'] = 'array';
$_SERVER['MAIL_MAILER'] = 'array';
