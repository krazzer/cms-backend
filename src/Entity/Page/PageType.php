<?php

namespace KikCMS\Entity\Page;

enum PageType: string
{
    case Menu = 'menu';
    case Page = 'page';
    case Link = 'link';
}
