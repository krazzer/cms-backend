<?php

namespace KikCMS\Domain\DataTable;

enum SourceType: string
{
    case Pdo = 'pdo';
    case Cache = 'cache';
    case Local = 'local';
}