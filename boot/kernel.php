<?php

use KikCMS\Kernel;

require_once dirname(__DIR__) . '/src/functions.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
