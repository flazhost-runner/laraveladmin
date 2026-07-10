<?php

namespace Modules\Setting\app\Services;

use App\Exceptions\AppException;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Modules\Home\app\Interfaces\IFeTemplateService;
use Modules\Setting\app\Interfaces\ISettingService;

class SettingService implements ISettingService
{
    public function __construct(
        private IFeTemplateService $feTemplateService,
    ) {}

    public function get(): Setting
    {
        $setting = Setting::first();

        if (! $setting) {
            $setting = new Setting;
            $setting->forceFill([
                'name' => 'LaravelAdmin',
                'theme' => 'blue',
                'fe_template' => config('fe_templates.default'),
            ]);
            $setting->save();
        }

        return $setting;
    }

    public function update(array $data, string $actorId): Setting
    {
        $setting = $this->get();

        // Validate the FE template slug pattern before saving (anti-SSRF).
        $feSlug = $data['fe_template'] ?? null;
        if ($feSlug !== null && ! $this->feTemplateService->isValidSlug($feSlug)) {
            throw new AppException('Template tidak dikenali', 400);
        }

        // Handle file uploads — driver-aware + konversi webp via storeImage()
        // (paritas NodeAdmin SettingService: kunci modules/setting/<timestamp>_<rand>).
        foreach (['icon', 'logo', 'login_image'] as $field) {
            if (! empty($data[$field]) && $data[$field] instanceof UploadedFile) {
                $stem = 'modules/setting/'.now()->format('Y-m-d_hi').strtolower(now()->format('a')).'_'.random_int(1, 10000);
                $data[$field] = storeImage($data[$field], $stem);
            } else {
                // Keep existing value if not a new upload
                unset($data[$field]);
            }
        }

        // Normalize theme to lowercase to match THEMES constant keys
        if (isset($data['theme'])) {
            $data['theme'] = strtolower($data['theme']);
        }

        // settings table has no created_by/updated_by columns — skip audit columns
        unset($data['updated_by']);
        $setting->forceFill($data);
        $setting->save();

        // Invalidate settings cache so changes show up immediately.
        Cache::forget('settings_current');

        // FE template changed → download on-demand (when not cached yet).
        // A failed download must not fail the save (landing falls back).
        if ($feSlug) {
            try {
                $this->feTemplateService->ensure($feSlug);
            } catch (AppException $e) {
                logger()->error("Unduh template frontend gagal: {$e->getMessage()}");
            }
        }

        return $setting->fresh();
    }
}
