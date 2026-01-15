<?php

namespace KikCMS\Domain\App\Exception;

use Exception;

class NotImplementedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Not implemented yet');
    }
}