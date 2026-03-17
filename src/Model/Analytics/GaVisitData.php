<?php declare(strict_types=1);

namespace KikCMS\Model\Analytics;

class GaVisitData
{
    const TABLE = 'cms_analytics_metric';
    const ALIAS = 'am';

    const FIELD_DATE   = 'date';
    const FIELD_TYPE   = 'type';
    const FIELD_VALUE  = 'value';
    const FIELD_VISITS = 'visits';
}