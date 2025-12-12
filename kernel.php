<?php

use KikCMS\Kernel;

require_once __DIR__ . '/src/functions.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
