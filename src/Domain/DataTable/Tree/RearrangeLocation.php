<?php

namespace App\Domain\DataTable\Tree;

enum RearrangeLocation: int
{
    case BEFORE = 0;
    case INSIDE = 1;
    case AFTER = 2;
}