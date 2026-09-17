<?php

namespace KikCMS\Domain\Form\Config;

use KikCMS\Domain\FrontendForm\ContactFormType;

class FormConfig
{
    const string FIELDS             = 'fields';
    const string FIELD_PROVIDER     = 'field_provider';
    const string FIELD_PROVIDER_ADD = 'field_provider_add';

    const string FORM_CONTACT = 'contact';

    const array FORM_CLASS_MAP = [
        self::FORM_CONTACT => ContactFormType::class,
    ];
}