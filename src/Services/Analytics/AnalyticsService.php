<?php declare(strict_types=1);

namespace KikCMS\Services\Analytics;

use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Entity\Analytics\GaDayVisit;
use KikCMS\Entity\Analytics\GaVisitData;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Core analytics service handling Google Analytics data import and reporting.
 */
class AnalyticsService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly CacheInterface $cache,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
        private readonly AnalyticsImportService $analyticsImportService,
        private readonly AnalyticsGoogleService $analyticsGoogleService,
        private readonly AnalyticsDataService $analyticsDataService,
        private readonly AnalyticsBulkInsertService $analyticsBulkInsertService,
    ) {}

    /**
     * Fetch statistics from Google and save them to the database.
     */
    public function importIntoDb(): bool
    {
        if ($this->isUpdating()) {
            return true;
        }

        $this->cache->delete(cacheConfig::STATS_UPDATE_IN_PROGRESS);
        $this->cache->get(cacheConfig::STATS_UPDATE_IN_PROGRESS, function () {
            return true;
        });

        $this->connection->beginTransaction();

        try {
            $version = $this->getAnalyticsVersion();

            if ($version == 4) {
                $visitData  = $this->analyticsDataService->getVisitData();
                $metricData = $this->analyticsDataService->getVisitMetricData();

                $this->analyticsBulkInsertService->insertBulk(GaDayVisit::TABLE, $visitData);
                $this->analyticsBulkInsertService->insertBulk(GaVisitData::TABLE, $metricData);

                $this->stopUpdatingForSixHours();
            } else {
                $results       = $this->analyticsGoogleService->getVisitData();
                $requireUpdate = $this->analyticsImportService->importVisitorMetrics();

                $results = array_map(function ($row) {
                    return [
                        'date'          => $row['ga:year'] . '-' . $row['ga:month'] . '-' . $row['ga:day'],
                        'visits'        => (int) $row['visits'],
                        'unique_visits' => (int) $row['visits'] * ($row['unique'] / 100),
                    ];
                }, $results);

                $this->truncateTable(GaDayVisit::TABLE);
                $this->analyticsBulkInsertService->insertBulk(GaDayVisit::TABLE, $results);

                if ( ! $requireUpdate) {
                    $this->stopUpdatingForSixHours();
                }
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            $this->connection->rollBack();
            $this->cache->delete(cacheConfig::STATS_UPDATE_IN_PROGRESS);
            $this->cache->delete(cacheConfig::STATS_REQUIRE_UPDATE);
            return false;
        }

        $this->cache->delete(cacheConfig::STATS_UPDATE_IN_PROGRESS);
        $this->connection->commit();

        return true;
    }

    /**
     * Get the maximum date from GaDayVisit table.
     */
    public function getMaxDate(): ?DateTime
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('MAX(date)')->from(GaDayVisit::TABLE);
        $result = $qb->executeQuery()->fetchOne();

        return $result ? new DateTime($result) : null;
    }

    /**
     * Get overview data for the given date range.
     */
    public function getOverviewData(?DateTime $start, ?DateTime $end): array
    {
        $totalVisits       = $this->getTotalVisits($start, $end);
        $totalUniqueVisits = $this->getTotalUniqueVisits($start, $end);
        $dailyAverage      = $this->getDailyAverage($start, $end);
        $monthlyAverage    = $this->getMonthlyAverage($start, $end);

        return [
            $this->translator->trans('statistics.overview.totalVisits')       => $totalVisits,
            $this->translator->trans('statistics.overview.totalUniqueVisits') => $totalUniqueVisits,
            $this->translator->trans('statistics.overview.dailyAverage')      => $dailyAverage,
            $this->translator->trans('statistics.overview.monthlyAverage')    => $monthlyAverage,
        ];
    }

    /**
     * Get visitor data (source, page, etc.) for the given date range.
     */
    public function getVisitorData(?DateTime $start = null, ?DateTime $end = null): array
    {
        $totalVisits = $this->getTotalVisits($start, $end);
        $visitorData = [];

        $qb = $this->connection->createQueryBuilder();
        $qb->select('type', 'value', 'SUM(visits) as visits')
            ->addSelect('ROUND((SUM(visits) / :totalVisits) * 100, 1) as percentage')
            ->from(GaVisitData::TABLE)
            ->groupBy('type, value')
            ->orderBy('visits', 'DESC')
            ->addOrderBy('value', 'ASC')
            ->setParameter('totalVisits', $totalVisits)
            ->setMaxResults(count(StatisticsConfig::GA_TYPES) * 50);

        $this->addDateWhere($qb, $start, $end, 'date');

        $results = $qb->executeQuery()->fetchAllAssociative();

        foreach ($results as $result) {
            $type = $result['type'];

            if ( ! array_key_exists($type, $visitorData)) {
                $visitorData[$type] = [];
            }

            if (count($visitorData[$type]) >= 25) {
                continue;
            }

            $visitorData[$type][] = $result;
        }

        return $visitorData;
    }

    /**
     * Get chart data for visitors (area chart) based on interval and date range.
     */
    public function getVisitorsChartData(string $interval, ?DateTime $start = null, ?DateTime $end = null): array
    {
        if ($interval === StatisticsConfig::VISITS_DAILY) {
            $qb = $this->connection->createQueryBuilder();
            $qb->select("DATE_FORMAT(date, '%Y-%m-%d') as date", 'SUM(visits) as visits', 'SUM(unique_visits) as unique_visits')
                ->from(GaDayVisit::TABLE)
                ->groupBy('date');
            $this->addDateWhere($qb, $start, $end, 'date');

            $result = $qb->executeQuery()->fetchAllAssociative();
            $result = $this->addMissingDays($result, $start, $end);

            $rows = [];
            foreach ($result as $row) {
                $rows[] = [
                    'c' => [
                        ['v' => $row['date']],
                        ['v' => (int) $row['visits']],
                        ['v' => (int) $row['unique_visits']],
                    ]
                ];
            }
        } else {
            $qb = $this->connection->createQueryBuilder();
            $qb->select("DATE_FORMAT(date, '%Y-%m') as month")
                ->addSelect('SUM(visits) as visits')
                ->addSelect('SUM(unique_visits) as unique_visits')
                ->from(GaDayVisit::TABLE)
                ->groupBy('month');
            $this->addDateWhere($qb, $start, $end, 'date');

            $rows = [];
            foreach ($qb->executeQuery()->fetchAllAssociative() as $row) {
                $rows[] = [
                    'c' => [
                        ['v' => $row['month']],
                        ['v' => (int) $row['visits']],
                        ['v' => (int) $row['unique_visits']],
                    ]
                ];
            }
        }

        $strVisitors       = $this->translator->trans('statistics.visitors');
        $strUniqueVisitors = $this->translator->trans('statistics.uniqueVisitors');

        $cols = [
            ['label' => '', 'type' => 'string'],
            ['label' => $strVisitors, 'type' => 'number'],
            ['label' => $strUniqueVisitors, 'type' => 'number'],
        ];

        return [
            'cols' => $cols,
            'rows' => $rows,
        ];
    }

    /**
     * Check if an update is currently in progress.
     */
    public function isUpdating(): bool
    {
        return $this->cache->hasItem(cacheConfig::STATS_UPDATE_IN_PROGRESS);
    }

    /**
     * Determine if the database requires an update from Google Analytics.
     */
    public function requiresUpdate(): bool
    {
        if ( ! $this->cache->hasItem(cacheConfig::STATS_REQUIRE_UPDATE)) {
            return true;
        }

        $requireUpdateFlag = $this->cache->get(cacheConfig::STATS_REQUIRE_UPDATE, function () {
            return false;
        });
        if ( ! $requireUpdateFlag) {
            return false;
        }

        $maxDate = $this->getMaxDate();

        // if there are 0 zero stats, or today isn't present yet
        if ( ! $maxDate || $maxDate->format('dmY') !== (new DateTime())->format('dmY')) {
            return true;
        }

        // if there are no visitor data stats
        $typeMaxDates = $this->getMaxDatePerVisitDataType();
        if ( ! $typeMaxDates) {
            return true;
        }

        // if there are no visitor data stats for today
        foreach ($typeMaxDates as $maxDate) {
            if ( ! $maxDate || $maxDate->format('dmY') !== (new DateTime())->format('dmY')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add date range conditions to a QueryBuilder.
     */
    private function addDateWhere(QueryBuilder $qb, ?DateTime $start, ?DateTime $end, string $dateField): void
    {
        if ($start) {
            $qb->andWhere($dateField . ' >= :dateStart')
                ->setParameter('dateStart', $start->format('Y-m-d'));
        }
        if ($end) {
            $qb->andWhere($dateField . ' <= :dateEnd')
                ->setParameter('dateEnd', $end->format('Y-m-d'));
        }
    }

    /**
     * Generate WHERE clause for date range (used in raw SQL).
     */
    private function getDateWhereClause(?DateTime $start, ?DateTime $end): string
    {
        $clause = '';
        if ($start) {
            $clause .= " AND date >= '" . $start->format('Y-m-d') . "'";
        }
        if ($end) {
            $clause .= " AND date <= '" . $end->format('Y-m-d') . "'";
        }
        return $clause;
    }

    /**
     * Get parameters for date where clause (for prepared statements).
     */
    private function getDateWhereParams(?DateTime $start, ?DateTime $end): array
    {
        $params = [];
        if ($start) {
            $params['dateStart'] = $start->format('Y-m-d');
        }
        if ($end) {
            $params['dateEnd'] = $end->format('Y-m-d');
        }
        return $params;
    }

    /**
     * Add missing days to daily chart data.
     */
    private function addMissingDays(array $visits, DateTime $start, DateTime $end): array
    {
        $dates = array_column($visits, 'date');
        if (empty($dates)) {
            return $visits;
        }

        $interval = new DateInterval('P1D');
        $period   = new DatePeriod($start, $interval, $end->modify('+1 day'));

        $existingDates = array_flip($dates);
        $filled        = [];

        foreach ($period as $dateObj) {
            $date = $dateObj->format('Y-m-d');
            if (isset($existingDates[$date])) {
                foreach ($visits as $row) {
                    if ($row['date'] === $date) {
                        $filled[] = $row;
                        break;
                    }
                }
            } else {
                $filled[] = [
                    'date'          => $date,
                    'visits'        => 0,
                    'unique_visits' => 0,
                ];
            }
        }

        return $filled;
    }

    /**
     * Get daily average visits.
     */
    private function getDailyAverage(?DateTime $start, ?DateTime $end): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('AVG(visits)')->from(GaDayVisit::TABLE);
        $this->addDateWhere($qb, $start, $end, 'date');
        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * Get monthly average visits (approximation).
     */
    private function getMonthlyAverage(?DateTime $start, ?DateTime $end): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('ROUND(AVG(visits) * 365.25 / 12)')->from(GaDayVisit::TABLE);
        $this->addDateWhere($qb, $start, $end, 'date');
        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * Get total visits.
     */
    private function getTotalVisits(?DateTime $start, ?DateTime $end): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('SUM(visits)')->from(GaDayVisit::TABLE);
        $this->addDateWhere($qb, $start, $end, 'date');
        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * Get total unique visits.
     */
    private function getTotalUniqueVisits(?DateTime $start, ?DateTime $end): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('SUM(unique_visits)')->from(GaDayVisit::TABLE);
        $this->addDateWhere($qb, $start, $end, 'date');
        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * Get maximum dates per visitor data type.
     */
    private function getMaxDatePerVisitDataType(): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('type, MAX(date) as max_date')
            ->from(GaVisitData::TABLE)
            ->groupBy('type');

        $results = $qb->executeQuery()->fetchAllKeyValue(); // returns [type => max_date]

        return array_map(function ($date) {
            return $date ? new DateTime($date) : null;
        }, $results);
    }

    /**
     * Prevent updates for six hours.
     */
    private function stopUpdatingForSixHours(): void
    {
        $this->cache->delete(cacheConfig::STATS_REQUIRE_UPDATE);
        $this->cache->get(cacheConfig::STATS_REQUIRE_UPDATE, function () {
            return false;
        }, 6 * 3600);
    }

    /**
     * Truncate a table.
     */
    private function truncateTable(string $table): void
    {
        $this->connection->executeStatement('TRUNCATE TABLE ' . $table);
    }

    /**
     * Placeholder for determining GA version. Adapt to your config source.
     */
    private function getAnalyticsVersion(): int
    {
        // For now, assume 4; you can inject a parameter or read from env.
        return 4;
    }
}