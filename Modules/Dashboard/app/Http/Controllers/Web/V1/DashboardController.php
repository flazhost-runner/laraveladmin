<?php

namespace Modules\Dashboard\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Modules\Dashboard\app\Interfaces\IDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private IDashboardService $dashboardService,
    ) {}

    public function index()
    {
        $stats = $this->dashboardService->getStats();
        $activities = $this->dashboardService->getRecentActivities();

        return view('dashboard-module::be.default.index', compact('stats', 'activities'));
    }
}
