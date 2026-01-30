#!/usr/bin/env php
<?php

use KikCMS\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

require_once dirname(__DIR__) . '/src/functions.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    return new Application($kernel);
};
