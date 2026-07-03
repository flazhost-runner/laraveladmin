<?php

namespace Modules\Profile\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Modules\Profile\app\Http\Requests\ChangePasswordRequest;
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

        return view('profile-module::be.default.profile', compact('user'));
    }

    public function update(ProfileUpdateRequest $request)
    {
        $userId = session('user_id');
        $this->profileService->update($userId, $request->validated());

        return redirect()->route('admin.v1.profile.index')->with('success', 'Update Profile Success.');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $userId = session('user_id');
        $this->profileService->changePassword($userId, $request->validated());

        return redirect()->route('admin.v1.profile.index')->with('success', 'Password changed successfully.');
    }
}
