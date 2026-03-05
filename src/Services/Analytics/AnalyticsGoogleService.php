<?php declare(strict_types=1);

namespace KikCMS\Services\Analytics;

use DateTime;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use KikCMS\Config\StatisticsConfig;

class AnalyticsGoogleService
{
    private Google_Service_AnalyticsReporting $analytics;
    private string $viewId;

    public function __construct(Google_Service_AnalyticsReporting $analytics, string $viewId)
    {
        $this->analytics = $analytics;
        $this->viewId = $viewId;
    }

    public function getVisitData(): array
    {
        return $this->getVisitorData(null, null, ["ga:percentNewSessions" => "unique"]);
    }

    public function getVisitorData(
        ?string $dimensionName = null,
        ?DateTime $fromDate = null,
        array $addMetrics = [],
        array $filters = []
    ): array {
        $fromDate = $fromDate ?: new DateTime('2005-01-01');

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($fromDate->format('Y-m-d'));
        $dateRange->setEndDate('today');

        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression('ga:visits');
        $sessions->setAlias('visits');
        $metrics = [$sessions];

        foreach ($addMetrics as $metricName => $alias) {
            $metric = new Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($metricName);
            $metric->setAlias($alias);
            $metrics[] = $metric;
        }

        $year = new Google_Service_AnalyticsReporting_Dimension();
        $year->setName('ga:year');
        $month = new Google_Service_AnalyticsReporting_Dimension();
        $month->setName('ga:month');
        $day = new Google_Service_AnalyticsReporting_Dimension();
        $day->setName('ga:day');
        $dimensions = [$year, $month, $day];

        if ($dimensionName) {
            $dimension = new Google_Service_AnalyticsReporting_Dimension();
            $dimension->setName($dimensionName);
            $dimensions[] = $dimension;
        }

        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics($metrics);
        $request->setDimensions($dimensions);
        $request->setPageSize(StatisticsConfig::MAX_IMPORT_ROWS);

        if ($filters) {
            foreach ($filters as $name => $value) {
                $request->setFiltersExpression($name . '==' . $value);
            }
        }

        return $this->reportRequestToArray($request);
    }

    private function reportRequestToArray(Google_Service_AnalyticsReporting_ReportRequest $request): array
    {
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests([$request]);

        $reports = $this->analytics->reports->batchGet($body);
        $results = [];

        foreach ($reports as $report) {
            $header           = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions() ?: [];
            $metricHeaders    = $header->getMetricHeader()->getMetricHeaderEntries() ?: [];
            $rows             = $report->getData()->getRows() ?: [];

            foreach ($rows as $row) {
                $results[] = $this->reportRowToArray($row, $metricHeaders, $dimensionHeaders);
            }
        }

        return $results;
    }

    private function reportRowToArray($reportRow, array $metricHeaders, array $dimensionHeaders): array
    {
        $resultRow = [];

        $dimensions = $reportRow->getDimensions() ?: [];
        $metrics    = $reportRow->getMetrics() ?: [];

        for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
            $resultRow[$dimensionHeaders[$i]] = $dimensions[$i];
        }

        if (!empty($metrics)) {
            $values = $metrics[0]->getValues() ?: [];
            for ($k = 0; $k < count($values) && $k < count($metricHeaders); $k++) {
                $entry                        = $metricHeaders[$k];
                $resultRow[$entry->getName()] = $values[$k];
            }
        }

        return $resultRow;
    }
}