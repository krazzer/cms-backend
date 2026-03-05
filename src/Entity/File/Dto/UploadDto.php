<?php

namespace KikCMS\Entity\File\Dto;

use KikCMS\Entity\File\File;

class UploadDto
{
    public ?File $folder = null;

    public function getFolder(): ?File
    {
        return $this->folder;
    }
}