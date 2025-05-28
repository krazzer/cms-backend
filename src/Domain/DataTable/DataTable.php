<?php

namespace App\Domain\DataTable;

class DataTable
{
    /** @var string */
    private string $instance;

    /** @var SourceType */
    private SourceType $source;

    /** @var string */
    private string $pdoModel;

    /** @var string */
    private string $cachePool;

    /** @var array */
    private array $headers;

    /** @var array */
    private array $buttons;

    /** @var array */
    private array $mobileColumns;

    /** @var array */
    private array $form;

    /** @var string */
    private string $langCode;

    /**
     * @return string
     */
    public function getInstance(): string
    {
        return $this->instance;
    }

    /**
     * @param string $instance
     * @return $this
     */
    public function setInstance(string $instance): DataTable
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * @return SourceType
     */
    public function getSource(): SourceType
    {
        return $this->source;
    }

    /**
     * @param SourceType $source
     * @return $this
     */
    public function setSource(SourceType $source): DataTable
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getPdoModel(): string
    {
        return $this->pdoModel;
    }

    /**
     * @param string $pdoModel
     * @return $this
     */
    public function setPdoModel(string $pdoModel): DataTable
    {
        $this->pdoModel = $pdoModel;
        return $this;
    }

    /**
     * @return string
     */
    public function getCachePool(): string
    {
        return $this->cachePool;
    }

    /**
     * @param string $cachePool
     * @return $this
     */
    public function setCachePool(string $cachePool): DataTable
    {
        $this->cachePool = $cachePool;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): DataTable
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array
     */
    public function getForm(): array
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getFormFields(): array
    {
        if(isset($this->form[DataTableConfig::KEY_FORM_TABS])){
            $fields = [];

            foreach ($this->form[DataTableConfig::KEY_FORM_TABS] as $tab){
                $fields = array_merge($fields, array_keys($tab[DataTableConfig::KEY_FORM_FIELDS]));
            }

            return $fields;
        }

        return array_keys($this->form[DataTableConfig::KEY_FORM_FIELDS] ?? []);
    }

    /**
     * @param array $form
     * @return $this
     */
    public function setForm(array $form): DataTable
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return array
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @param array $buttons
     * @return $this
     */
    public function setButtons(array $buttons): DataTable
    {
        $this->buttons = $buttons;
        return $this;
    }

    /**
     * @return array
     */
    public function getMobileColumns(): array
    {
        return $this->mobileColumns;
    }

    /**
     * @param array $mobileColumns
     * @return $this
     */
    public function setMobileColumns(array $mobileColumns): DataTable
    {
        $this->mobileColumns = $mobileColumns;
        return $this;
    }

    /**
     * @return string
     */
    public function getLangCode(): string
    {
        return $this->langCode;
    }

    /**
     * @param string $langCode
     * @return $this
     */
    public function setLangCode(string $langCode): DataTable
    {
        $this->langCode = $langCode;
        return $this;
    }
}