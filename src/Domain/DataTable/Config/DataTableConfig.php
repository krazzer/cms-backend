<?php

namespace KikCMS\Domain\DataTable\Config;

class DataTableConfig
{
    const string FIELD_TYPE_SELECT    = 'select';
    const string FIELD_TYPE_DATATABLE = 'datatable';

    const string CELL_TYPE_CHECKBOX = 'checkbox';

    const string FORM_FIELDS = 'fields';
    const string FORM_FIELD  = 'field';
    const string FORM_TABS   = 'tabs';

    const string FIELD_TYPE     = 'type';
    const string FIELD_SETTINGS = 'settings';
    const string FIELD_INSTANCE = 'instance';
    const string FIELD_ITEMS    = 'items';

    const string HELPER_DATA     = 'data';
    const string HELPER_SETTINGS = 'settings';

    const string SESSION_KEY_LANG = 'dataTableLang';

    const string PATH_SEPARATOR = '.';
    const string PATH_LOCALE    = '*';

    const string DEFAULT_TABLE_ALIAS = 'e';

    const string SORT_ASC  = 'ascending';
    const string SORT_DESC = 'descending';

    const array SORT_MAP_SQL = [
        self::SORT_ASC  => 'ASC',
        self::SORT_DESC => 'DESC',
    ];
}