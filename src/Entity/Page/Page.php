<?php

namespace App\Entity\Page;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'cms_page')]
class Page
{
    const string FIELD_ID            = 'id';
    const string FIELD_PARENTS       = 'parents';
    const string FIELD_DISPLAY_ORDER = 'display_order';
    const string FIELD_CHILDREN      = 'children'; // not an actual field, but derived from other data
    const string FIELD_TYPE          = 'type';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $parents = null;

    #[ORM\Column(nullable: true)]
    private ?array $name = null;

    #[ORM\Column(nullable: true)]
    private ?array $active = null;

    #[ORM\Column(nullable: true)]
    private ?array $slug = null;

    #[ORM\Column(nullable: true)]
    private ?array $seo = null;

    #[ORM\Column(nullable: true)]
    private ?array $content = null;

    #[ORM\Column(nullable: true)]
    private ?int $alias = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $template = null;

    #[ORM\Column(nullable: true)]
    private ?int $display_order = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(nullable: true)]
    private ?int $menu_max_level = null;

    #[ORM\Column(insertable: false)]
    private ?DateTimeImmutable $created_at = null;

    #[ORM\Column(insertable: false)]
    private ?DateTimeImmutable $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getParents(): ?array
    {
        return $this->parents;
    }

    public function setParents(?array $parents): static
    {
        $this->parents = $parents;

        return $this;
    }

    public function getAlias(): ?int
    {
        return $this->alias;
    }

    public function setAlias(?int $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): static
    {
        $this->template = $template;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content): Page
    {
        $this->content = $content;
        return $this;
    }

    public function getName(): ?array
    {
        return $this->name;
    }

    public function setName(?array $name): Page
    {
        $this->name = $name;
        return $this;
    }

    public function getActive(): ?array
    {
        return $this->active;
    }

    public function setActive(?array $active): Page
    {
        $this->active = $active;
        return $this;
    }

    public function getSlug(): ?array
    {
        return $this->slug;
    }

    public function setSlug(?array $slug): Page
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSeo(): ?array
    {
        return $this->seo;
    }

    public function setSeo(?array $seo): Page
    {
        $this->seo = $seo;
        return $this;
    }

    public function getMenuMaxLevel(): ?int
    {
        return $this->menu_max_level;
    }

    public function setMenuMaxLevel(?int $menu_max_level): Page
    {
        $this->menu_max_level = $menu_max_level;
        return $this;
    }
}
