<?php declare(strict_types=1);

namespace KikCMS\Model\Analytics;

class GaDayVisit
{
    const TABLE = 'cms_analytics_day';
    const ALIAS = 'ad';

    const FIELD_DATE          = 'date';
    const FIELD_VISITS        = 'visits';
    const FIELD_UNIQUE_VISITS = 'unique_visits';
}