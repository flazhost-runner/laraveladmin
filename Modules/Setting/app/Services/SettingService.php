<?php

namespace Modules\Setting\app\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Modules\Setting\app\Interfaces\IFeCatalogService;
use Modules\Setting\app\Interfaces\ISettingService;

class SettingService implements ISettingService
{
    public function __construct(
        private IFeCatalogService $feCatalogService,
    ) {}

    public function get(): Setting
    {
        $setting = Setting::first();

        if (! $setting) {
            $setting = new Setting;
            $setting->forceFill([
                'name' => 'LaravelAdmin',
                'theme' => 'blue',
                'fe_template' => 'agency-consulting-002-creative-agency',
            ]);
            $setting->save();
        }

        return $setting;
    }

    public function update(array $data, string $actorId): Setting
    {
        $setting = $this->get();

        $oldTemplate = $setting->fe_template;
        $newTemplate = $data['fe_template'] ?? $oldTemplate;

        // Handle file uploads
        foreach (['icon', 'logo', 'login_image'] as $field) {
            if (! empty($data[$field]) && $data[$field] instanceof UploadedFile) {
                $path = $data[$field]->store("settings/{$field}", 'public');
                $data[$field] = $path;
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

        // If fe_template changed, ensure new template is cached
        if ($newTemplate !== $oldTemplate) {
            $this->feCatalogService->ensure($newTemplate);
        }

        // Invalidate settings cache
        Cache::forget('settings_current');

        return $setting->fresh();
    }
}
