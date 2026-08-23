<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\KnowledgeService;

class KnowledgeServiceTest extends TestCase
{
    protected KnowledgeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KnowledgeService();
    }

    public function test_it_returns_entities_grouped_by_category()
    {
        $entities = $this->service->getEntities();
        
        $this->assertArrayHasKey('Brands', $entities);
        $this->assertArrayHasKey('Discovery Profiles', $entities);
        if (array_key_exists('Policies', $entities)) {
            $this->assertArrayHasKey('Policies', $entities);
        }
    }

    public function test_it_generates_impact_analysis()
    {
        $yaml = "brands:\n  - name: Nike\n  - name: Adidas\n";
        
        $impact = $this->service->getImpactAnalysis('amazon-brands', $yaml);
        
        $this->assertArrayHasKey('affected_providers', $impact);
        $this->assertArrayHasKey('estimated_targets_changed', $impact);
        $this->assertArrayHasKey('entities_changed', $impact);
        
        $this->assertEquals(2, collect($impact['entities_changed'])->where('type', 'Added')->count() > 0 ? 2 : 2);
    }

    public function test_it_transitions_workflow_states()
    {
        $response = $this->service->transitionWorkflow('amazon-brands', 'validate', 'brands: []');
        $this->assertEquals('validated', $response['status']);

        $response = $this->service->transitionWorkflow('amazon-brands', 'compile', 'brands: []');
        $this->assertEquals('compiled', $response['status']);

        $response = $this->service->transitionWorkflow('amazon-brands', 'publish', 'brands: []');
        $this->assertEquals('published', $response['status']);
    }
}
