<?php

namespace KikCMS\Domain\DataTable\Rearrange;

enum RearrangeLocation: int
{
    case BEFORE = 0;
    case INSIDE = 1;
    case AFTER = 2;
}