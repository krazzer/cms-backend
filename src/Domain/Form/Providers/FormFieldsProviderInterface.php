<?php

namespace KikCMS\Domain\Form\Providers;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kikcms.form_fields_provider')]
interface FormFieldsProviderInterface
{
    public function getFieldsConfig(): array;
}