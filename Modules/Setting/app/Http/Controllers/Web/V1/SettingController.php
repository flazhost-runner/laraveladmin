<?php

namespace Modules\Setting\app\Http\Controllers\Web\V1;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Setting\app\Http\Requests\SettingUpdateRequest;
use Modules\Setting\app\Interfaces\IFeCatalogService;
use Modules\Setting\app\Interfaces\ISettingService;

class SettingController extends Controller
{
    public function __construct(
        private ISettingService $settingService,
        private IFeCatalogService $feCatalogService,
    ) {}

    public function index(Request $request)
    {
        $setting = $this->settingService->get();

        // FE catalog: server-side pagination + filter (q_name/q_category/
        // q_page). The active template is pinned to the first page.
        $filter = $request->only(['q_name', 'q_category', 'q_page', 'q_page_size']);
        $feActive = trim((string) ($setting->fe_template ?? '')) ?: config('fe_templates.default');
        $result = $this->feCatalogService->paginate($filter, $feActive);

        return view('setting-module::be.default.index', [
            'setting' => $setting,
            'themes' => config('themes', []),
            'feTemplates' => $result['datas'],
            'feCategories' => $this->feCatalogService->categories(),
            'feActive' => $feActive,
            'paginateData' => $result['paginate_data'],
            'filter' => $filter,
        ]);
    }

    public function update(SettingUpdateRequest $request)
    {
        $actorId = session('user_id');
        $this->settingService->update($request->validated(), $actorId);

        return redirect()->route('admin.v1.setting.index')->with('success', 'Save Setting Success.');
    }

    /** Raw HTML preview of one FE template (thumbnail/modal; cached client-side). */
    public function fePreview(string $slug)
    {
        try {
            $html = $this->feCatalogService->previewHtml($slug);

            return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
        } catch (AppException $e) {
            return response($e->getMessage(), $e->getCode() ?: 502)
                ->header('Content-Type', 'text/plain; charset=utf-8');
        }
    }
}
