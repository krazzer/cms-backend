<?php

namespace KikCMS\Entity\KeyValue;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cms_keyvalue')]
class KeyValue
{
    #[ORM\Id]
    #[ORM\Column(name: "item_id", type: "binary", length: 255, index: true)]
    private string $id;

    #[ORM\Column(name: "item_data", type: "blob", nullable: false)]
    private mixed $data = null;

    #[ORM\Column(name: "item_json", type: "json_pretty", nullable: true)]
    private mixed $json = null;

    #[ORM\Column(name: "item_lifetime", type: "integer", nullable: true, options: ["unsigned" => true])]
    private ?int $lifetime = null;

    #[ORM\Column(name: "item_time", type: "integer", nullable: false, options: ["unsigned" => true])]
    private int $time;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getJson(): ?array
    {
        return $this->json;
    }

    public function setJson(?array $json): static
    {
        $this->json = $json;
        return $this;
    }

    public function getLifetime(): ?int
    {
        return $this->lifetime;
    }

    public function setLifetime(?int $lifetime): static
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): static
    {
        $this->time = $time;
        return $this;
    }

    public function setItemJson(mixed $item = null): static
    {
        $this->json = $item;
        return $this;
    }

    public function getItemJson(): mixed
    {
        return $this->json;
    }
}