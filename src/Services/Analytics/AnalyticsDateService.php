<?php declare(strict_types=1);

namespace KikCMS\Services\Analytics;

use DateTime;
use Doctrine\DBAL\Connection;
use KikCMS\Model\Analytics\GaVisitData;

class AnalyticsDateService
{
    private Connection $connection;

    // Todo: Gebruik constructor property promotion ipv deze oude manier
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the maximum date for a given metric type (from GaVisitData).
     */
    public function getMaxMetricDate(string $type): ?DateTime
    {
        // Todo: Doctrine entity gebruiken
        $qb = $this->connection->createQueryBuilder();
        $qb->select('MAX(date)')
            ->from(GaVisitData::TABLE)
            ->where('type LIKE :type')
            ->setParameter('type', $type . '%');

        $result = $qb->executeQuery()->fetchOne();

        return $result ? new DateTime($result) : null;
    }
}