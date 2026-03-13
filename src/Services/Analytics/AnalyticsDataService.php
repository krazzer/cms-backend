<?php declare(strict_types=1);

namespace KikCMS\Services\Analytics;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use KikCMS\Config\GaConfig;
use KikCMS\Config\StatisticsConfig;
use KikCMS\Model\Analytics\GaDayVisit;
use KikCMS\Model\Analytics\GaVisitData;

/**
 * Service for handling the analytics v4 using the new (2022) Analytics Data API.
 */
class AnalyticsDataService
{
    // Todo: Gebruik constructor property promotion ipv deze oude manier
    private AnalyticsDateService $analyticsDateService;
    private BetaAnalyticsDataClient $betaAnalyticsDataClient;
    private string $propertyId;

    public function __construct(
        AnalyticsDateService $analyticsDateService,
        BetaAnalyticsDataClient $betaAnalyticsDataClient,
        string $propertyId
    )
    {
        $this->analyticsDateService    = $analyticsDateService;
        $this->betaAnalyticsDataClient = $betaAnalyticsDataClient;
        $this->propertyId              = $propertyId;
    }

    public function getVisitData(): array
    {
        $dateRange = new DateRange([
            'start_date' => GaConfig::GA4_LAUNCH_DATE,
            'end_date'   => 'today'
        ]);

        $dimension = new Dimension(['name' => 'date']);

        $metricSessions = new Metric(['name' => 'sessions']);
        $metricUsers    = new Metric(['name' => 'activeUsers']);

        $orderBy = new OrderBy([
            'dimension' => new DimensionOrderBy(['dimension_name' => 'date'])
        ]);

        $request = new RunReportRequest([
            'property'    => 'properties/' . $this->propertyId,
            'date_ranges' => [$dateRange],
            'dimensions'  => [$dimension],
            'metrics'     => [$metricSessions, $metricUsers],
            'order_bys'   => [$orderBy],
        ]);

        $response = $this->betaAnalyticsDataClient->runReport($request);

        $results = [];

        foreach ($response->getRows() as $row) {
            $sessions = $row->getMetricValues()[0]->getValue();
            $users    = $row->getMetricValues()[1]->getValue();

            $results[] = [
                GaDayVisit::FIELD_DATE          => $row->getDimensionValues()[0]->getValue(),
                GaDayVisit::FIELD_VISITS        => $sessions,
                GaDayVisit::FIELD_UNIQUE_VISITS => min($users, $sessions),
            ];
        }

        return $results;
    }

    /**
     * Get metric data for a specific dimension, optionally filtered by device category.
     *
     * @param string $dimension
     * @param string $metric
     * @param string|null $subDimension
     * @return array
     */
    public function getMetricData(string $dimension, string $metric, ?string $subDimension = null): array
    {
        $dimensions = [
            new Dimension(['name' => 'date']),
            new Dimension(['name' => $dimension]),
        ];

        if ($subDimension) {
            $dimensions[] = new Dimension(['name' => 'deviceCategory']);
        }

        $lastUpdateDate = $this->analyticsDateService->getMaxMetricDate($metric);
        $lastUpdate     = $lastUpdateDate ? $lastUpdateDate->format('Y-m-d') : GaConfig::GA4_LAUNCH_DATE;

        $dateRange = new DateRange(['start_date' => $lastUpdate, 'end_date' => 'today']);

        $metricSessions = new Metric(['name' => 'sessions']);
        $metricUsers    = new Metric(['name' => 'activeUsers']);

        $orderBy = new OrderBy([
            'dimension' => new DimensionOrderBy(['dimension_name' => 'date'])
        ]);

        $request = new RunReportRequest([
            'property'    => 'properties/' . $this->propertyId,
            'dimensions'  => $dimensions,
            'date_ranges' => [$dateRange],
            'metrics'     => [$metricSessions, $metricUsers],
            'order_bys'   => [$orderBy],
        ]);

        $response = $this->betaAnalyticsDataClient->runReport($request);

        $results = [];

        foreach ($response->getRows() as $row) {
            $type = $metric;

            if ($subDimension) {
                $type .= ucfirst($row->getDimensionValues()[2]->getValue());
            }

            $value = $row->getDimensionValues()[1]->getValue();

            // the empty value in the path is the same as /, so replace it to merge
            if ($metric === GaConfig::METRIC_PATH && $value === '') {
                $value = '/';
            }

            if ( ! array_key_exists($type, StatisticsConfig::GA_TYPES)) {
                continue;
            }

            $results[] = [
                GaVisitData::FIELD_DATE   => $row->getDimensionValues()[0]->getValue(),
                GaVisitData::FIELD_VISITS => $row->getMetricValues()[0]->getValue(),
                GaVisitData::FIELD_TYPE   => $type,
                GaVisitData::FIELD_VALUE  => $value,
            ];
        }

        return $results;
    }

    /**
     * Get all visit metric data (source, os, path, browser, country, resolution).
     *
     * @return array
     */
    public function getVisitMetricData(): array
    {
        $resSource     = $this->getMetricData(GaConfig::DIMENSION_SOURCE, GaConfig::METRIC_SOURCE);
        $resOs         = $this->getMetricData(GaConfig::DIMENSION_OS, GaConfig::METRIC_OS);
        $resPath       = $this->getMetricData(GaConfig::DIMENSION_PATH, GaConfig::METRIC_PATH);
        $resBrowser    = $this->getMetricData(GaConfig::DIMENSION_BROWSER, GaConfig::METRIC_BROWSER);
        $resCountry    = $this->getMetricData(GaConfig::DIMENSION_COUNTRY, GaConfig::METRIC_COUNTRY);
        $resResolution = $this->getMetricData(GaConfig::DIMENSION_RESOLUTION, GaConfig::METRIC_RESOLUTION,
            GaConfig::DIMENSION_DEVICECATEGORY);

        return array_merge($resSource, $resOs, $resPath, $resBrowser, $resCountry, $resResolution);
    }
}