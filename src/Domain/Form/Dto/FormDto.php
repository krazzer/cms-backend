<?php

namespace KikCMS\Domain\Form\Dto;

use KikCMS\Domain\Form\Form;
use Symfony\Component\Serializer\Attribute\SerializedName;

class FormDto
{
    #[SerializedName('name')]
    public Form $form;

    public function getForm(): Form
    {
        return $this->form;
    }
}