<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\DataTable;
use Symfony\Component\Serializer\Attribute\SerializedName;

class Dto
{
    #[SerializedName('instance')]
    public DataTable $dataTable;

    public function getDataTable(): DataTable
    {
        return $this->dataTable;
    }
}