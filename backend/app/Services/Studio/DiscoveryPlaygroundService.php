<?php

namespace App\Services\Studio;

class DiscoveryPlaygroundService
{
    /**
     * Simulates the Discovery Engine's URL generation based on constraints.
     */
    public function simulateDiscovery(string $provider, array $constraints): array
    {
        $urls = [];
        $parameters = [];
        
        $brand = $constraints['brand'] ?? '';
        $nodeId = $constraints['node_id'] ?? '';
        $minDiscount = $constraints['min_discount'] ?? '';

        if (strtolower($provider) === 'amazon') {
            // Simulate Amazon URL builder
            $baseUrl = "https://www.amazon.in/s";
            
            if ($nodeId) {
                $parameters['rh'] = "n:{$nodeId}";
            }
            if ($brand) {
                // If rh already exists, append brand constraint
                $parameters['rh'] = isset($parameters['rh']) ? $parameters['rh'] . ",p_89:{$brand}" : "p_89:{$brand}";
            }
            if ($minDiscount) {
                // Amazon discount param p_8: e.g., p_8:30-100 means 30% to 100% off
                $parameters['rh'] = isset($parameters['rh']) ? $parameters['rh'] . ",p_8:{$minDiscount}-100" : "p_8:{$minDiscount}-100";
            }
            
            $query = http_build_query($parameters);
            $urls[] = $baseUrl . ($query ? '?' . $query : '');
            
            // Add a paginated example
            $urls[] = $baseUrl . ($query ? '?' . $query . '&page=2' : '?page=2');

        } elseif (strtolower($provider) === 'flipkart') {
            // Simulate Flipkart URL builder
            $baseUrl = "https://www.flipkart.com/search";
            
            if ($nodeId) {
                $parameters['p[]'] = "facets.category[]%253D{$nodeId}";
            }
            if ($brand) {
                $parameters['p[]'] = isset($parameters['p[]']) ? $parameters['p[]'] . "&p[]=facets.brand%253D{$brand}" : "facets.brand%253D{$brand}";
            }
            if ($minDiscount) {
                $parameters['p[]'] = isset($parameters['p[]']) ? $parameters['p[]'] . "&p[]=facets.discount%253D{$minDiscount}%2525%2B" : "facets.discount%253D{$minDiscount}%2525%2B";
            }
            
            // Flipkart uses complex p[] arrays, this is simplified for simulation output
            $query = "";
            foreach($parameters as $key => $val) {
                $query .= ($query ? "&{$key}={$val}" : "{$key}={$val}");
            }

            $urls[] = $baseUrl . ($query ? '?' . $query : '');
            $urls[] = $baseUrl . ($query ? '?' . $query . '&page=2' : '?page=2');
        }

        return [
            'provider' => $provider,
            'constraints_applied' => count(array_filter($constraints)),
            'parameters' => $parameters,
            'generated_urls' => $urls,
            'estimated_targets' => rand(150, 4500)
        ];
    }
}
