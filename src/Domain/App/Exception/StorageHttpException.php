<?php

namespace KikCMS\Domain\App\Exception;


use Symfony\Component\HttpKernel\Exception\HttpException;

class StorageHttpException extends HttpException
{
    public function __construct(?string $message = null)
    {
        parent::__construct(500, $message);
    }
}