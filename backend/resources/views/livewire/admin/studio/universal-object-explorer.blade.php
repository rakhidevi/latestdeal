<div>
    <!-- Workspace Header -->
    <div class="mb-4 border-b border-gray-200 pb-4 sm:flex sm:items-center sm:justify-between">
        <div>
            <h3 class="text-2xl font-bold leading-6 text-gray-900">Universal Object Explorer</h3>
            <p class="mt-2 text-sm text-gray-500">Inspect the absolute state of any entity across the Discovery Platform.</p>
        </div>
        <div class="mt-3 sm:ml-4 sm:mt-0 flex w-full max-w-md">
            <input type="text" wire:model.defer="searchQuery" wire:keydown.enter="performSearch" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-mono" placeholder="Search UUID, ASIN, Trace, Worker, Policy...">
            <button wire:click="performSearch" class="ml-2 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Explore</button>
        </div>
    </div>

    @if($relationshipGraph)
    <div class="flex h-[800px] border border-gray-200 rounded-lg overflow-hidden bg-white shadow">
        
        <!-- Left Pane: Explorer Tree (VS Code Style) -->
        <div class="w-1/3 border-r border-gray-200 bg-gray-50 flex flex-col">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-100 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Explorer</span>
            </div>
            <div class="flex-1 overflow-y-auto p-4 font-mono text-sm">
                <!-- Recursive Tree Partial (Simulated visually for this artifact) -->
                @php
                    $renderTree = function($node, $level = 0) use (&$renderTree, $selectedNodeId) {
                        $isSelected = $node['id'] === $selectedNodeId;
                        $padding = $level * 1.5;
                        
                        $typeColor = match($node['type']) {
                            'Trace' => 'text-purple-600',
                            'SearchTargetDTO' => 'text-blue-600',
                            'UniversalProductDTO' => 'text-indigo-600',
                            'CanonicalDealDTO' => 'text-green-600',
                            'Policy' => 'text-yellow-600',
                            'EvidenceRecord' => 'text-red-600',
                            default => 'text-gray-600'
                        };

                        echo "<div class='mb-1'>";
                        echo "<div wire:click=\"selectNode('{$node['id']}')\" class='flex items-center cursor-pointer hover:bg-gray-200 py-1 px-2 rounded-md transition-colors " . ($isSelected ? 'bg-indigo-100 text-indigo-900 ring-1 ring-indigo-300' : 'text-gray-700') . "' style='padding-left: {$padding}rem;'>";
                        
                        // Chevron for branches
                        if (!empty($node['children'])) {
                            echo "<svg class='w-4 h-4 mr-1 text-gray-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'></path></svg>";
                        } else {
                            echo "<span class='w-5'></span>"; // Spacer
                        }

                        echo "<span class='font-bold mr-2 {$typeColor}'>[" . substr($node['type'], 0, 4) . "]</span>";
                        echo "<span class='truncate'>" . htmlspecialchars($node['name']) . "</span>";
                        echo "</div>";

                        if (!empty($node['children'])) {
                            echo "<div>";
                            foreach ($node['children'] as $child) {
                                $renderTree($child, $level + 1);
                            }
                            echo "</div>";
                        }
                        echo "</div>";
                    };
                @endphp

                @php $renderTree($relationshipGraph); @endphp
            </div>
        </div>

        <!-- Right Pane: Universal Inspector Renderer -->
        <div class="w-2/3 bg-white relative flex flex-col">
            @if($selectedNodeData)
                <!-- Reusing the Pure Renderer we built in Sprint 5.1 -->
                <livewire:admin.studio.universal-inspector :nodeData="$selectedNodeData" :key="$selectedNodeData['id']" />
            @else
                <div class="flex h-full items-center justify-center text-gray-500">
                    <p>Select a node in the Explorer to inspect.</p>
                </div>
            @endif
        </div>

    </div>
    @else
    <div class="text-center mt-32">
        <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <h3 class="mt-4 text-sm font-semibold text-gray-900">Welcome to the Object Explorer</h3>
        <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">Enter any identifier in the search bar above to instantly resolve its graph and inspect its lifecycle.</p>
    </div>
    @endif
</div>
