<?php

namespace Modules\Setting\app\Interfaces;

use App\Models\Setting;

interface ISettingService
{
    public function get(): Setting;

    public function update(array $data, string $actorId): Setting;
}
