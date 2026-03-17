<?php declare(strict_types=1);

namespace KikCMS\Config;

class StatisticsConfig
{
    public const VISITS_DAILY   = 'daily';
    public const VISITS_MONTHLY = 'monthly';

    public const TYPE_SOURCE             = 'source';
    public const TYPE_OS                 = 'os';
    public const TYPE_PAGE               = 'page';
    public const TYPE_BROWSER            = 'browser';
    public const TYPE_LOCATION           = 'location';
    public const TYPE_RESOLUTION_DESKTOP = 'resolutionDesktop';
    public const TYPE_RESOLUTION_TABLET  = 'resolutionTablet';
    public const TYPE_RESOLUTION_MOBILE  = 'resolutionMobile';

    public const MAX_IMPORT_ROWS = 10000;

    public const GA_TYPES = [
        self::TYPE_SOURCE             => 'ga:source',
        self::TYPE_OS                 => 'ga:operatingSystem',
        self::TYPE_PAGE               => 'ga:pagePath',
        self::TYPE_BROWSER            => 'ga:browser',
        self::TYPE_LOCATION           => 'ga:city',
        self::TYPE_RESOLUTION_DESKTOP => ['ga:screenResolution', ['ga:deviceCategory' => 'desktop']],
        self::TYPE_RESOLUTION_TABLET  => ['ga:screenResolution', ['ga:deviceCategory' => 'tablet']],
        self::TYPE_RESOLUTION_MOBILE  => ['ga:screenResolution', ['ga:deviceCategory' => 'mobile']],
    ];
}