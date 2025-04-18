<?php

namespace App\Entity\DataTable;

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
    private array $form;

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
     * @param array $form
     * @return $this
     */
    public function setForm(array $form): DataTable
    {
        $this->form = $form;
        return $this;
    }
}