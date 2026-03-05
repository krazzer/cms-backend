<?php

namespace KikCMS\Domain\App\Statistics;

use KikCMS\Config\StatisticsConfig;
use KikCMS\Services\Analytics\AnalyticsService;
use KikCMS\Services\Util\DateTimeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class StatisticsController extends AbstractController
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private DateTimeService $dateTimeService
    ) {}

    #[Route('/api/statistics/visitors', name: 'statistics_visitors', methods: ['POST'])]
    public function getVisitorsAction(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $interval = $data['interval'] ?? StatisticsConfig::VISITS_MONTHLY;
        $start    = $this->dateTimeService->getFromDatePickerValue($data['start'] ?? null);
        $end      = $this->dateTimeService->getFromDatePickerValue($data['end'] ?? null);

        $visitorsData   = $this->analyticsService->getVisitorsChartData($interval, $start, $end);
        $visitorData    = $this->analyticsService->getVisitorData($start, $end);
        $overviewData   = $this->analyticsService->getOverviewData($start, $end);
        $requiresUpdate = $this->analyticsService->requiresUpdate();

        return new JsonResponse([
            'visitorsData'   => $visitorsData,
            'visitorData'    => $visitorData,
            'overviewData'   => $overviewData,
            'requiresUpdate' => $requiresUpdate,
        ]);
    }

    #[Route('/api/statistics/update', name: 'statistics_update', methods: ['POST'])]
    public function updateAction(Request $request): JsonResponse
    {
        $data  = $request->toArray();
        $token = $data['token'] ?? $request->request->get('token');
        if ( ! $token) {
            throw new AccessDeniedException('Missing security token.');
        }

        if ($this->analyticsService->isUpdating()) {
            while ($this->analyticsService->isUpdating()) {
                sleep(1);
            }
            return new JsonResponse(['success' => true]);
        }

        $success = $this->analyticsService->importIntoDb();
        $maxDate = $this->analyticsService->getMaxDate();

        return new JsonResponse([
            'success' => $success,
            'maxDate' => $maxDate?->format('Y-m-d'),
        ]);
    }
}