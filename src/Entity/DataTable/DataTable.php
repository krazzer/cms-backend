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
}