<?php declare(strict_types=1);

namespace KikCMS\Services\Analytics;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Entity\Analytics\GaVisitData;

/**
 * Service for importing various visitor metrics from Google Analytics.
 */
class AnalyticsImportService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
        private readonly AnalyticsGoogleService $analyticsGoogleService,
        private readonly AnalyticsBulkInsertService $analyticsBulkInsertService,
    ) {}

    /**
     * Import various info about visitors.
     *
     * @return bool True if more data is available (max rows reached)
     */
    public function importVisitorMetrics(): bool
    {
        $requireUpdate = false;

        foreach (StatisticsConfig::GA_TYPES as $type => $dimension) {
            if (is_array($dimension)) {
                $filters   = $dimension[1];
                $dimension = $dimension[0];
            } else {
                $filters = [];
            }

            $fromDate    = $this->getTypeLastUpdate($type);
            $visitorData = $this->analyticsGoogleService->getVisitorData($dimension, $fromDate, [], $filters);
            $insertData  = $this->getInsertDataByVisitorData($visitorData, $dimension, $type);

            if ($fromDate) {
                $this->connection->delete(GaVisitData::TABLE, [
                    GaVisitData::FIELD_DATE => $fromDate->format('Y-m-d'),
                    GaVisitData::FIELD_TYPE => $type,
                ]);
            }

            $this->analyticsBulkInsertService->insertBulk(GaVisitData::TABLE, $insertData);

            if (count($visitorData) == StatisticsConfig::MAX_IMPORT_ROWS) {
                $requireUpdate = true;
            }
        }

        return $requireUpdate;
    }

    /**
     * Get the last update date for a given metric type.
     */
    private function getTypeLastUpdate(string $type): ?DateTime
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('MAX(g.date)')
            ->from(GaVisitData::class, 'g')
            ->where('g.type = :type')
            ->setParameter('type', $type);

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result ? new DateTime($result) : null;
    }

    /**
     * Convert visitor data rows (from Google API) to database insert format.
     */
    private function getInsertDataByVisitorData(array $results, string $dimension, string $type): array
    {
        $insertData = [];

        foreach ($results as $resultRow) {
            $date  = $resultRow['ga:year'] . '-' . $resultRow['ga:month'] . '-' . $resultRow['ga:day'];
            $value = $resultRow[$dimension];

            // Handle excessively long values (as in the original code)
            if (strlen($value) > 128) {
                $value = substr($value, 0, 115) . uniqid();
            }

            $insertRow = [
                GaVisitData::FIELD_DATE   => $date,
                GaVisitData::FIELD_TYPE   => $type,
                GaVisitData::FIELD_VALUE  => $value,
                GaVisitData::FIELD_VISITS => (int) $resultRow['visits'],
            ];

            $insertData[] = $insertRow;
        }

        return $insertData;
    }
}