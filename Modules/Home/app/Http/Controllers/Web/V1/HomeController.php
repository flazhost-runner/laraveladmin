<?php

namespace Modules\Home\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\Home\app\Interfaces\IHomeService;

class HomeController extends Controller
{
    public function __construct(private IHomeService $homeService) {}

    /**
     * Serve the public landing page (/).
     */
    public function root(): Response
    {
        $html = $this->homeService->getActiveLanding();

        return response($html, 200)->header('Content-Type', 'text/html');
    }

    /**
     * Alias for root() (/home).
     */
    public function index(): Response
    {
        return $this->root();
    }
}
