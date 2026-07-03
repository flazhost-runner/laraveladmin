<?php

namespace Modules\Setting\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Setting\app\Http\Requests\SettingUpdateRequest;
use Modules\Setting\app\Interfaces\IFeCatalogService;
use Modules\Setting\app\Interfaces\ISettingService;

class SettingController extends Controller
{
    private const THEMES = [
        'blue' => ['#3B82F6', '#60A5FA', '#EFF6FF', '#1E40AF'],
        'purple' => ['#8B5CF6', '#A78BFA', '#F5F3FF', '#5B21B6'],
        'green' => ['#10B981', '#34D399', '#ECFDF5', '#065F46'],
        'orange' => ['#F59E0B', '#FCD34D', '#FFFBEB', '#92400E'],
        'red' => ['#EF4444', '#F87171', '#FEF2F2', '#991B1B'],
    ];

    public function __construct(
        private ISettingService $settingService,
        private IFeCatalogService $feCatalogService,
    ) {}

    public function index(Request $request)
    {
        $setting = $this->settingService->get();
        $filter = $request->only(['q_name', 'fe_page', 'q_category']);
        $catalog = $this->feCatalogService->getCatalog($filter);
        $themes = self::THEMES;

        return view('setting-module::be.default.index', compact('setting', 'catalog', 'filter', 'themes'));
    }

    public function update(SettingUpdateRequest $request)
    {
        $actorId = session('user_id');
        $this->settingService->update($request->validated(), $actorId);

        return redirect()->route('admin.v1.setting.index')->with('success', 'Save Setting Success.');
    }

    public function fePreview(string $slug)
    {
        try {
            $html = $this->feCatalogService->previewHtml($slug);

            return response($html, 200)->header('Content-Type', 'text/html');
        } catch (\InvalidArgumentException $e) {
            abort(400, $e->getMessage());
        } catch (\RuntimeException $e) {
            abort(502, $e->getMessage());
        }
    }
}
