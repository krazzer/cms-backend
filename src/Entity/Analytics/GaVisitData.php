<?php declare(strict_types=1);

namespace KikCMS\Entity\Analytics;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cms_analytics_metric')]
#[ORM\UniqueConstraint(columns: ['date', 'type', 'value'])]
class GaVisitData
{
    public const string TABLE = 'cms_analytics_metric';

    public const string FIELD_DATE   = 'date';
    public const string FIELD_TYPE   = 'type';
    public const string FIELD_VALUE  = 'value';
    public const string FIELD_VISITS = 'visits';

    #[ORM\Id]
    #[ORM\Column(type: 'date')]
    private DateTimeInterface $date;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 100)]
    private string $type;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $value;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $visits = null;

    public function __construct(DateTimeInterface $date, string $type, string $value, ?int $visits = null)
    {
        $this->date   = $date;
        $this->type   = $type;
        $this->value  = $value;
        $this->visits = $visits;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getVisits(): ?int
    {
        return $this->visits;
    }

    public function setVisits(?int $visits): self
    {
        $this->visits = $visits;
        return $this;
    }
}