<?php

namespace Modules\Profile\app\Interfaces;

use App\Models\User;

interface IProfileService
{
    public function get(string $userId): User;

    public function update(string $userId, array $data): User;
}
