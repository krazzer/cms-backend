<?php

namespace App\Domain\DataTable\Dto;

use App\Domain\DataTable\DataTable;
use Symfony\Component\Serializer\Attribute\SerializedName;

class DataTableDto
{
    #[SerializedName('instance')]
    public DataTable $dataTable;

    public function getDataTable(): DataTable
    {
        return $this->dataTable;
    }
}