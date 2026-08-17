<?php

namespace App\Services\Studio;

class StudioAPI
{
    protected WidgetRegistry $widgetRegistry;
    protected WorkspaceManager $workspaceManager;
    protected TraceService $traceService;
    protected EventService $eventService;
    protected NotificationService $notificationService;
    protected PermissionService $permissionService;
    protected DataPlatformService $dataPlatformService;
    protected TraceSearchService $traceSearchService;
    protected TimelineService $timelineService;
    protected PipelineService $pipelineService;
    protected ObjectGraphService $objectGraphService;
    protected OperationsService $operationsService;
    protected KnowledgeService $knowledgeService;
    protected PolicySimulatorService $policySimulatorService;
    protected DiscoveryPlaygroundService $discoveryPlaygroundService;
    protected StrategyAnalyticsService $strategyAnalyticsService;
    protected AdminControlService $adminControlService;
    protected RunbookService $runbookService;

    public function __init(
        WidgetRegistry $widgetRegistry,
        WorkspaceManager $workspaceManager,
        TraceService $traceService,
        EventService $eventService,
        NotificationService $notificationService,
        PermissionService $permissionService,
        DataPlatformService $dataPlatformService,
        TraceSearchService $traceSearchService,
        TimelineService $timelineService,
        PipelineService $pipelineService,
        ObjectGraphService $objectGraphService,
        OperationsService $operationsService,
        KnowledgeService $knowledgeService,
        PolicySimulatorService $policySimulatorService,
        DiscoveryPlaygroundService $discoveryPlaygroundService,
        StrategyAnalyticsService $strategyAnalyticsService,
        AdminControlService $adminControlService,
        RunbookService $runbookService
    ) {
        $this->widgetRegistry = $widgetRegistry;
        $this->workspaceManager = $workspaceManager;
        $this->traceService = $traceService;
        $this->eventService = $eventService;
        $this->notificationService = $notificationService;
        $this->permissionService = $permissionService;
        $this->dataPlatformService = $dataPlatformService;
        $this->traceSearchService = $traceSearchService;
        $this->timelineService = $timelineService;
        $this->pipelineService = $pipelineService;
        $this->objectGraphService = $objectGraphService;
        $this->operationsService = $operationsService;
        $this->knowledgeService = $knowledgeService;
        $this->policySimulatorService = $policySimulatorService;
        $this->discoveryPlaygroundService = $discoveryPlaygroundService;
        $this->strategyAnalyticsService = $strategyAnalyticsService;
        $this->adminControlService = $adminControlService;
        $this->runbookService = $runbookService;
    }

    /**
     * Facade interface to the Studio services so Livewire components 
     * only ever need to inject the StudioAPI.
     */
    public function widgets(): WidgetRegistry
    {
        return $this->widgetRegistry;
    }

    public function workspace(): WorkspaceManager
    {
        return $this->workspaceManager;
    }

    public function traces(): TraceService
    {
        return $this->traceService;
    }

    public function events(): EventService
    {
        return $this->eventService;
    }

    public function notifications(): NotificationService
    {
        return $this->notificationService;
    }

    public function permissions(): PermissionService
    {
        return $this->permissionService;
    }

    public function dataPlatform(): DataPlatformService
    {
        return $this->dataPlatformService;
    }

    public function search(): TraceSearchService
    {
        return $this->traceSearchService;
    }

    public function timeline(): TimelineService
    {
        return $this->timelineService;
    }

    public function pipeline(): PipelineService
    {
        return $this->pipelineService;
    }

    public function explorer(): ObjectGraphService
    {
        return $this->objectGraphService;
    }

    public function operations(): OperationsService
    {
        return $this->operationsService;
    }

    public function knowledge(): KnowledgeService
    {
        return $this->knowledgeService;
    }

    public function simulator(): PolicySimulatorService
    {
        return $this->policySimulatorService;
    }

    public function playground(): DiscoveryPlaygroundService
    {
        return $this->discoveryPlaygroundService;
    }

    public function analytics(): StrategyAnalyticsService
    {
        return $this->strategyAnalyticsService;
    }

    public function admin(): AdminControlService
    {
        return $this->adminControlService;
    }

    public function runbooks(): RunbookService
    {
        return $this->runbookService;
    }
}
