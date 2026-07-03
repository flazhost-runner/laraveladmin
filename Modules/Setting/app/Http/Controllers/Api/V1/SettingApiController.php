<?php

namespace Modules\Setting\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Setting\app\Interfaces\ISettingService;

class SettingApiController extends Controller
{
    public function __construct(
        private ISettingService $settingService,
    ) {}

    public function index()
    {
        try {
            $setting = $this->settingService->get();

            return response()->json(['status' => true, 'message' => 'OK', 'data' => [
                'id' => $setting->id,
                'name' => $setting->name ?? '',
                'theme' => $setting->theme ?? '',
                'fe_template' => $setting->fe_template ?? '',
            ]]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 500);
        }
    }
}
