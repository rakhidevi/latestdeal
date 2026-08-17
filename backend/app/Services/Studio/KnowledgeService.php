<?php

namespace App\Services\Studio;

class KnowledgeService
{
    /**
     * Fetch entities for the KMS sidebar navigation.
     */
    public function getEntities(): array
    {
        return [
            'Brands' => [
                ['id' => 'amazon-brands', 'name' => 'amazon/brands.yaml', 'status' => 'compiled'],
                ['id' => 'flipkart-brands', 'name' => 'flipkart/brands.yaml', 'status' => 'draft_pending']
            ],
            'Categories' => [
                ['id' => 'global-categories', 'name' => 'core/categories.yaml', 'status' => 'compiled']
            ],
            'Nodes' => [
                ['id' => 'amazon-nodes', 'name' => 'amazon/nodes.yaml', 'status' => 'compiled']
            ],
            'Sellers' => [
                ['id' => 'trusted-sellers', 'name' => 'global/sellers.yaml', 'status' => 'compiled']
            ],
            'Discovery Profiles' => [
                ['id' => 'profile-electronics', 'name' => 'profiles/electronics.yaml', 'status' => 'compiled']
            ]
        ];
    }

    /**
     * Simulates fetching the contents of a YAML file
     */
    public function getKnowledgeFile(string $id): array
    {
        // Simulated lookup
        return [
            'id' => $id,
            'name' => 'amazon/brands.yaml',
            'version' => 'v1.4.2',
            'last_modified' => now()->subHours(2)->toIso8601String(),
            'author' => 'ops-user',
            'content' => "brands:\n  - name: Nike\n    aliases: [\"Nike Inc\", \"Nike Sports\"]\n    trust_score: 0.95\n  - name: Adidas\n    aliases: [\"Adidas Originals\"]\n    trust_score: 0.92\n",
            'status' => 'draft' // Workflow state: edit, validate, preview, compile, diff, approve, publish
        ];
    }

    /**
     * Simulates compiling YAML into JSON and calculating the impact
     */
    public function getImpactAnalysis(string $id, string $yamlContent): array
    {
        // In a real system, this compares the compiled output of the new YAML 
        // against the active UCDP Knowledge graph.
        
        $lines = explode("\n", $yamlContent);
        $addedBrands = 0;
        foreach ($lines as $line) {
            if (str_contains($line, '- name:')) {
                $addedBrands++;
            }
        }

        return [
            'affected_providers' => ['Amazon'],
            'affected_discovery_profiles' => ['Electronics Targeter', 'Fashion Crawler'],
            'estimated_targets_changed' => '+14,250',
            'policies_impacted' => ['Brand Protection Policy v2'],
            'entities_changed' => [
                ['type' => 'Added', 'entity' => "$addedBrands new brands detected"],
                ['type' => 'Modified', 'entity' => 'Nike (trust_score updated)']
            ],
            'risk_level' => 'LOW',
            'is_valid' => true
        ];
    }

    /**
     * Executes the KMS State Machine transition
     */
    public function transitionWorkflow(string $id, string $action, string $content): array
    {
        $statusMap = [
            'save' => 'draft',
            'validate' => 'validated',
            'compile' => 'compiled',
            'approve' => 'approved',
            'publish' => 'published',
            'rollback' => 'rolled_back'
        ];

        return [
            'id' => $id,
            'status' => $statusMap[$action] ?? 'error',
            'message' => "Successfully executed {$action}."
        ];
    }
}
