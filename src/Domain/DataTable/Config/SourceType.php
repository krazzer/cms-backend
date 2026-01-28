<?php

namespace KikCMS\Domain\DataTable\Config;

enum SourceType: string
{
    case Pdo = 'pdo';
    case Cache = 'cache';
    case Local = 'local';
}