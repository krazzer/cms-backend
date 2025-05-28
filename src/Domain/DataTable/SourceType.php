<?php

namespace App\Domain\DataTable;

enum SourceType
{
    case Pdo;
    case Cache;
}