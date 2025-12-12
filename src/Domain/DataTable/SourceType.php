<?php

namespace KikCMS\Domain\DataTable;

enum SourceType
{
    case Pdo;
    case Cache;
}