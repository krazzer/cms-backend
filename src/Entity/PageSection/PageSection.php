<?php

namespace KikCMS\Entity\PageSection;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use KikCMS\Entity\Page\Page;

#[ORM\Entity(repositoryClass: PageSectionRepository::class)]
#[ORM\Table(name: 'cms_page_section')]
class PageSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'sections')]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Page $page;

    #[ORM\Column]
    private string $type;

    #[ORM\Column(type: 'json_pretty', nullable: true)]
    private ?array $content = null;

    #[ORM\Column(nullable: true)]
    private ?int $display_order = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function setPage(Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getDisplayOrder(): ?int
    {
        return $this->display_order;
    }

    public function setDisplayOrder(?int $display_order): static
    {
        $this->display_order = $display_order;

        return $this;
    }
}
