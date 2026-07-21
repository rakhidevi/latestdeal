<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Catalog\CatalogIntegrityService;

class CatalogHealthController extends Controller
{
    public function show(CatalogIntegrityService $integrityService)
    {
        $report = $integrityService->getHealthReport();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($report);
        }

        return view('admin.catalog_health', compact('report'));
    }
}
