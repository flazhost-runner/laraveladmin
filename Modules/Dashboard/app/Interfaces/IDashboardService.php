<?php

namespace Modules\Dashboard\app\Interfaces;

interface IDashboardService
{
    public function getStats(): array;

    public function getRecentActivities(): array;
}
