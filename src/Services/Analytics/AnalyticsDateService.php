<?php declare(strict_types=1);

namespace KikCMS\Services\Analytics;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use KikCMS\Model\Analytics\GaVisitData;

class AnalyticsDateService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Get the maximum date for a given metric type (from GaVisitData).
     */
    public function getMaxMetricDate(string $type): ?DateTime
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('MAX(g.date)')
            ->from(GaVisitData::class, 'g')
            ->where('g.type LIKE :type')
            ->setParameter('type', $type . '%');

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result ? new DateTime($result) : null;
    }
}