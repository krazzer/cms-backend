<?php

namespace App\Entity\DataTable;

enum SourceType
{
    case Pdo;
    case Cache;
}