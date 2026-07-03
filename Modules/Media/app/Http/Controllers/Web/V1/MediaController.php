<?php

namespace Modules\Media\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Media\app\Interfaces\IMediaService;

class MediaController extends Controller
{
    public function __construct(private IMediaService $mediaService) {}

    public function list()
    {
        $files = $this->mediaService->list();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $files,
        ]);
    }

    public function upload(Request $request)
    {
        // CSRF validated via x-csrf-token header in middleware
        $file = $request->file('file');
        if (! $file) {
            return response()->json(['status' => false, 'message' => 'No file uploaded.', 'data' => null], 422);
        }

        try {
            $result = $this->mediaService->upload($file);

            return response()->json([
                'status' => true,
                'message' => 'Upload Success.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 422);
        }
    }

    public function delete(Request $request)
    {
        $key = $request->input('key', '');
        if (! $key) {
            return response()->json(['status' => false, 'message' => 'Key is required.', 'data' => null], 422);
        }

        try {
            $this->mediaService->delete($key);

            return response()->json(['status' => true, 'message' => 'Delete Success.', 'data' => null]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 500);
        }
    }
}
