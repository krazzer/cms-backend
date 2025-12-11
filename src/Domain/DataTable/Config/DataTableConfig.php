<?php

namespace App\Domain\DataTable\Config;

class DataTableConfig
{
    const string FIELD_TYPE_SELECT = 'select';

    const string CELL_TYPE_CHECKBOX = 'checkbox';

    const string KEY_FORM_FIELDS = 'fields';
    const string KEY_FORM_FIELD  = 'field';
    const string KEY_FORM_TABS   = 'tabs';

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