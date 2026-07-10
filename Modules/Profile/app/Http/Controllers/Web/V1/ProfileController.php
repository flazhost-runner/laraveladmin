<?php

namespace Modules\Profile\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Modules\Profile\app\Http\Requests\ProfileUpdateRequest;
use Modules\Profile\app\Interfaces\IProfileService;

class ProfileController extends Controller
{
    public function __construct(
        private IProfileService $profileService,
    ) {}

    public function index()
    {
        $user = auth_user();
        $timezones = \DateTimeZone::listIdentifiers();

        return view('profile-module::be.default.profile', compact('user', 'timezones'));
    }

    public function update(ProfileUpdateRequest $request)
    {
        $userId = session('user_id');
        $this->profileService->update($userId, $request->validated());

        // Paritas NodeAdmin ProfileController.update: redirect ke dashboard.
        return redirect()->route('admin.v1.dashboard.index')->with('success', 'Update Profile Success.');
    }
}
