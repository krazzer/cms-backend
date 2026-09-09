<?php declare(strict_types=1);

namespace KikCMS\Entity\Analytics;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;

readonly class AnalyticsDateService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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