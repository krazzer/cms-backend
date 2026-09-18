<?php

namespace KikCMS\Domain\FrontendForm;

abstract class MailFormType extends FormType
{
    abstract public function getSubject(): string;
}