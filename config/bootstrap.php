<?php

use Symfony\Component\Dotenv\Dotenv;

if ( ! isset($_SERVER['APP_ENV']) && is_file(dirname(__DIR__) . '/.env')) {
    $dotenv = new Dotenv();

    $cmsEnv = dirname(__DIR__) . '/.env';

    if (isset($_ENV['PROJECT_ROOT'])) {
        $appEnv = $_ENV['PROJECT_ROOT'] . '/.env';
        $dotenv->load($appEnv);
    }

    $dotenv->load($cmsEnv);
}