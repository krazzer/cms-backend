<?php

namespace KikCMS\Entity\File;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use KikCMS\Entity\User\User;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Table(name: 'cms_file')]
class File
{
    public const string FIELD_ID          = 'id';
    public const string FIELD_NAME        = 'name';
    public const string FIELD_EXTENSION   = 'extension';
    public const string FIELD_MIMETYPE    = 'mimetype';
    public const string FIELD_CREATED     = 'created';
    public const string FIELD_UPDATED     = 'updated';
    public const string FIELD_IS_FOLDER   = 'is_folder';
    public const string FIELD_FOLDER_ID   = 'folder_id';
    public const string FIELD_SIZE        = 'size';
    public const string FIELD_USER_ID     = 'user_id';
    public const string FIELD_KEY         = 'key';
    public const string FIELD_HASH        = 'hash';
    public const string FIELD_CHILDREN    = 'children'; // derived
    public const string FIELD_PAGE_IMAGES = 'pageImages'; // derived

    public const IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $extension = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $mimetype = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $created = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updated = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isFolder = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'folder_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?self $folder = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'folder')]
    private Collection $children;

    #[ORM\Column(type: 'integer')]
    private ?int $size = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    #[ORM\Column(name: '`key`', type: 'string', length: 255, nullable: true)]
    private ?string $key = null;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $hash = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): static
    {
        $this->extension = $extension;
        return $this;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function setMimetype(?string $mimetype): static
    {
        $this->mimetype = $mimetype;
        return $this;
    }

    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(DateTimeImmutable $created): static
    {
        $this->created = $created;
        return $this;
    }

    public function getUpdated(): ?DateTimeImmutable
    {
        return $this->updated;
    }

    public function setUpdated(DateTimeImmutable $updated): static
    {
        $this->updated = $updated;
        return $this;
    }

    public function isFolder(): ?bool
    {
        return $this->isFolder;
    }

    public function setIsFolder(?bool $isFolder): static
    {
        $this->isFolder = $isFolder;
        return $this;
    }

    public function getFolder(): ?self
    {
        return $this->folder;
    }

    public function setFolder(?self $folder): static
    {
        $this->folder = $folder;
        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(iterable $children): static
    {
        $this->children = new ArrayCollection();

        foreach ($children as $child) {
            $child->setFolder($this);
            $this->children->add($child);
        }

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): static
    {
        $this->key = $key;
        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): static
    {
        $this->hash = $hash;
        return $this;
    }

    public function isImage(): bool
    {
        return in_array($this->getMimetype(), self::IMAGE_TYPES);
    }

    public function getFileName(bool $private = false, ?string $extension = null): string
    {
        $name = $private ? $this->getHash() : (string) $this->getId();
        return $name . '.' . ($extension ?: $this->getExtension());
    }

    public function getFilePath(string $storageDir): string
    {
        return rtrim($storageDir, '/') . '/' . $this->getFileName();
    }

    public function secondsUpdated(): int
    {
        return $this->getUpdated()->getTimestamp() - $this->getCreated()->getTimestamp();
    }
}