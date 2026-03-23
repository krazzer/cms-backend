<?php

namespace KikCMS\Entity\Analytics;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cms_analytics_day')]
class GaDayVisit
{
    public const string TABLE = 'cms_analytics_day';

    public const string FIELD_DATE          = 'date';
    public const string FIELD_VISITS        = 'visits';
    public const string FIELD_UNIQUE_VISITS = 'unique_visits';

    #[ORM\Id]
    #[ORM\Column(type: 'date')]
    private DateTimeInterface $date;

    #[ORM\Column(type: 'integer')]
    private int $visits;

    #[ORM\Column(name: 'unique_visits', type: 'integer')]
    private int $uniqueVisits;

    public function __construct(DateTimeInterface $date, int $visits, int $uniqueVisits)
    {
        $this->date         = $date;
        $this->visits       = $visits;
        $this->uniqueVisits = $uniqueVisits;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function setVisits(int $visits): self
    {
        $this->visits = $visits;
        return $this;
    }

    public function getUniqueVisits(): int
    {
        return $this->uniqueVisits;
    }

    public function setUniqueVisits(int $uniqueVisits): self
    {
        $this->uniqueVisits = $uniqueVisits;
        return $this;
    }
}