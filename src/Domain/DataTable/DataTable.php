<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\DataTable\Config\DataTableConfig;

class DataTable
{
    private SourceType $source;
    private string $instance;
    private string $pdoModel;
    private string $cachePool;
    private array $headers;
    private array $buttons;
    private array $mobileColumns;
    private array $form;
    private array $cells;
    private array $searchColumns;
    private string $langCode;
    private ?string $query;
    private ?string $modify;

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function setInstance(string $instance): DataTable
    {
        $this->instance = $instance;
        return $this;
    }

    public function getSource(): SourceType
    {
        return $this->source;
    }

    public function setSource(SourceType $source): DataTable
    {
        $this->source = $source;
        return $this;
    }

    public function getPdoModel(): string
    {
        return $this->pdoModel;
    }

    public function setPdoModel(string $pdoModel): DataTable
    {
        $this->pdoModel = $pdoModel;
        return $this;
    }

    public function getCachePool(): string
    {
        return $this->cachePool;
    }

    public function setCachePool(string $cachePool): DataTable
    {
        $this->cachePool = $cachePool;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): DataTable
    {
        $this->headers = $headers;
        return $this;
    }

    public function getForm(): array
    {
        return $this->form;
    }

    public function getFormFields(): array
    {
        if (isset($this->form[DataTableConfig::KEY_FORM_TABS])) {
            $fields = [];

            foreach ($this->form[DataTableConfig::KEY_FORM_TABS] as $tab) {
                $fields = array_merge($fields, $tab[DataTableConfig::KEY_FORM_FIELDS]);
            }

            return $fields;
        }

        return $this->form[DataTableConfig::KEY_FORM_FIELDS] ?? [];
    }

    public function getFormFieldKeys(): array
    {
        return array_keys($this->getFormFields());
    }

    public function setForm(array $form): DataTable
    {
        $this->form = $form;
        return $this;
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function setButtons(array $buttons): DataTable
    {
        $this->buttons = $buttons;
        return $this;
    }

    public function getMobileColumns(): array
    {
        return $this->mobileColumns;
    }

    public function setMobileColumns(array $mobileColumns): DataTable
    {
        $this->mobileColumns = $mobileColumns;
        return $this;
    }

    public function getLangCode(): string
    {
        return $this->langCode;
    }

    public function setLangCode(string $langCode): DataTable
    {
        $this->langCode = $langCode;
        return $this;
    }

    public function getCells(): array
    {
        return $this->cells;
    }

    public function setCells(array $cells): DataTable
    {
        $this->cells = $cells;
        return $this;
    }

    public function getClass(): ?string
    {
        return 'default';
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): DataTable
    {
        $this->query = $query;
        return $this;
    }

    public function getModify(): ?string
    {
        return $this->modify;
    }

    public function setModify(?string $modify): DataTable
    {
        $this->modify = $modify;
        return $this;
    }

    /**
     * Returns a map of form field keys with the corresponding field
     */
    public function getFormFieldMap(): array
    {
        $fieldMap = [];

        foreach ($this->getFormFields() as $key => $field) {
            $fieldMap[$key] = $field[DataTableConfig::KEY_FORM_FIELD] ?? $key;
        }

        return $fieldMap;
    }

    public function getSearchColumns(): array
    {
        return $this->searchColumns;
    }

    public function setSearchColumns(array $searchColumns): DataTable
    {
        $this->searchColumns = $searchColumns;
        return $this;
    }

    public function getSearch(): bool
    {
        return (bool) $this->searchColumns;
    }
}
