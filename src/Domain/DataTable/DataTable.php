<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\DataTable\Config\SourceType;
use KikCMS\Domain\Form\Form;

class DataTable
{
    private SourceType $source;
    private Form $form;
    private string $instance;
    private ?string $pdoModel = null;
    private string $cachePool;
    private array $actions;
    private array $headers;
    private array $buttons;
    private array $mobileColumns;
    private array $cells;
    private array $searchColumns;
    private array $typeForms;
    private ?string $query;
    private ?string $modify;
    private string $class = 'default';
    private bool $rearrange = false;

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

    public function getPdoModel(): ?string
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

    public function getForm(?string $type = null): Form
    {
        if( ! $type){
            return $this->form;
        }

        return $this->getTypeForms()[$type] ?? $this->form;
    }

    public function setForm(Form $form): DataTable
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

    public function getCells(): array
    {
        return $this->cells;
    }

    public function setCells(array $cells): DataTable
    {
        $this->cells = $cells;
        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
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

    public function setClass(string $class): DataTable
    {
        $this->class = $class;
        return $this;
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

    public function getTypeForms(): array
    {
        return $this->typeForms;
    }

    public function setTypeForms(array $typeForms): DataTable
    {
        $this->typeForms = $typeForms;
        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): DataTable
    {
        $this->actions = $actions;
        return $this;
    }

    public function isRearrange(): bool
    {
        return $this->rearrange;
    }

    public function setRearrange(bool $rearrange): DataTable
    {
        $this->rearrange = $rearrange;
        return $this;
    }
}
