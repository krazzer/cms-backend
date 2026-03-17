<?php

namespace KikCMS\Config;

class CacheConfig
{
    const ONE_MINUTE = 60;
    const ONE_HOUR   = 3600;
    const ONE_DAY    = self::ONE_HOUR * 24;
    const ONE_YEAR   = self::ONE_DAY * 365;
    const FOREVER    = self::ONE_YEAR * 10 ** 6; // a million years enough?

    const LANGUAGES         = 'languages';
    const TRANSLATION       = 'translation';
    const USER_TRANSLATIONS = 'userTranslations';

    const PAGE_LANGUAGE_FOR_URL = 'pageLanguageForUrl';
    const PAGE_LANGUAGE_FOR_KEY = 'pageLanguageForKey';
    const PAGE_FOR_KEY          = 'pageForKey';
    const OTHER_LANG_MAP        = 'otherLangMap';
    const URL                   = 'url';
    const URL_FOR_KEY           = 'urlForKey';
    const MENU                  = 'menu';
    const MENU_PAGES            = 'menuPages';
    const EXISTING_PAGE_CACHE   = 'existingPageCache';
    const PAGE_404              = 'page404';
    const FILE_ID_BY_KEY        = 'fileIdByKey';

    const STATS_REQUIRE_UPDATE = 'statsRequireUpdate';

    const STATS_UPDATE_IN_PROGRESS = 'statsUpdateInProgress';

    const SEPARATOR    = '.';
    const ALIAS_PREFIX = 'a';
}