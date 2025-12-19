<?php

namespace KikCMS\Entity\Page;

use KikCMS\Domain\DataTable\TableRow\TableViewRow;

class PageTableViewRow extends TableViewRow
{
    public ?bool $collapsed = null;
    public bool $children;
    public int $level;
    public string $type;

    public function __construct(TableViewRow $tableViewRow)
    {
        parent::__construct($tableViewRow->getId(), $tableViewRow->getRawRow(), $tableViewRow->getFilteredRow());
    }

    public function getCollapsed(): ?bool
    {
        return $this->collapsed;
    }

    public function setCollapsed(?bool $collapsed): PageTableViewRow
    {
        $this->collapsed = $collapsed;
        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): PageTableViewRow
    {
        $this->level = $level;
        return $this;
    }

    public function getChildren(): bool
    {
        return $this->children;
    }

    public function setChildren(bool $children): PageTableViewRow
    {
        $this->children = $children;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): PageTableViewRow
    {
        $this->type = $type;
        return $this;
    }

    public function toArray(): array
    {
        $arrayData = parent::toArray();

        $arrayData['children']  = $this->children;
        $arrayData['level']     = $this->level;
        $arrayData['type']      = $this->type;
        $arrayData['collapsed'] = $this->collapsed;

        return $arrayData;
    }
}