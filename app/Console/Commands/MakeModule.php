<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeModule extends Command
{
    protected $signature = 'admin:make-module
                            {name : Module name in PascalCase (e.g. Product)}
                            {--web-only : Scaffold web routes only, skip API routes}
                            {--api-only : Scaffold API routes only, skip web routes}';

    protected $description = 'Create a new LaravelAdmin module with complete structure';

    public function handle(): int
    {
        $name = $this->argument('name');

        // 1. Validate PascalCase
        if (! preg_match('/^[A-Z][A-Za-z0-9]+$/', $name)) {
            $this->error('Module name must be PascalCase (e.g. Product, BlogPost).');

            return self::FAILURE;
        }

        $webOnly = $this->option('web-only');
        $apiOnly = $this->option('api-only');

        if ($webOnly && $apiOnly) {
            $this->error('Cannot use --web-only and --api-only together.');

            return self::FAILURE;
        }

        $lower = strtolower($name);
        $moduleBase = base_path("Modules/{$name}");

        if (File::isDirectory($moduleBase)) {
            $this->error("Module [{$name}] already exists at {$moduleBase}");

            return self::FAILURE;
        }

        // 2. Create directory structure
        $dirs = [
            'app/Http/Controllers/Web/V1',
            'app/Http/Controllers/Api/V1',
            'app/Http/Requests',
            'app/Interfaces',
            'app/Services',
            'app/Providers',
            'database/migrations',
            "resources/views/be/default/{$lower}",
            'routes',
            'tests/Feature',
        ];

        foreach ($dirs as $dir) {
            File::makeDirectory("{$moduleBase}/{$dir}", 0755, true, true);
        }

        $created = [];

        // 3. Stub files
        // --- Interface ---
        $interfaceFile = "{$moduleBase}/app/Interfaces/I{$name}Service.php";
        File::put($interfaceFile, $this->stubInterface($name));
        $created[] = $interfaceFile;

        // --- Service ---
        $serviceFile = "{$moduleBase}/app/Services/{$name}Service.php";
        File::put($serviceFile, $this->stubService($name));
        $created[] = $serviceFile;

        // --- Web Controller ---
        if (! $apiOnly) {
            $webCtrlFile = "{$moduleBase}/app/Http/Controllers/Web/V1/{$name}Controller.php";
            File::put($webCtrlFile, $this->stubWebController($name, $lower));
            $created[] = $webCtrlFile;
        }

        // --- API Controller ---
        if (! $webOnly) {
            $apiCtrlFile = "{$moduleBase}/app/Http/Controllers/Api/V1/{$name}Controller.php";
            File::put($apiCtrlFile, $this->stubApiController($name, $lower));
            $created[] = $apiCtrlFile;
        }

        // --- ServiceProvider ---
        $providerFile = "{$moduleBase}/app/Providers/{$name}ServiceProvider.php";
        File::put($providerFile, $this->stubServiceProvider($name, $lower, $webOnly, $apiOnly));
        $created[] = $providerFile;

        // --- Routes ---
        if (! $apiOnly) {
            $webRouteFile = "{$moduleBase}/routes/web.php";
            File::put($webRouteFile, $this->stubWebRoutes($name, $lower));
            $created[] = $webRouteFile;
        }
        if (! $webOnly) {
            $apiRouteFile = "{$moduleBase}/routes/api.php";
            File::put($apiRouteFile, $this->stubApiRoutes($name, $lower));
            $created[] = $apiRouteFile;
        }

        // --- module.json ---
        $moduleJsonFile = "{$moduleBase}/module.json";
        File::put($moduleJsonFile, $this->stubModuleJson($name));
        $created[] = $moduleJsonFile;

        // --- Tests ---
        $testFile = "{$moduleBase}/tests/Feature/{$name}Test.php";
        File::put($testFile, $this->stubTest($name, $lower));
        $created[] = $testFile;

        // --- Views ---
        if (! $apiOnly) {
            $viewBase = "{$moduleBase}/resources/views/be/default/{$lower}";

            $indexView = "{$viewBase}/index.blade.php";
            File::put($indexView, $this->stubViewIndex($name, $lower));
            $created[] = $indexView;

            $createView = "{$viewBase}/create.blade.php";
            File::put($createView, $this->stubViewCreate($name, $lower));
            $created[] = $createView;

            $editView = "{$viewBase}/edit.blade.php";
            File::put($editView, $this->stubViewEdit($name, $lower));
            $created[] = $editView;
        }

        // 4. Output created files
        $this->info("Module [{$name}] scaffolded successfully!");
        $this->newLine();
        $this->line('<fg=green>Files created:</>');
        foreach ($created as $file) {
            $this->line('  '.str_replace(base_path().'/', '', $file));
        }

        // 5. Next steps
        $this->newLine();
        $this->line('<fg=yellow>Next steps:</>');
        $this->line('  1. composer dump-autoload');
        $this->line('  2. php artisan config:clear');
        $this->line("  3. Fill in I{$name}Service interface methods");
        $this->line("  4. Implement {$name}Service");
        $this->line('  5. Add FormRequest validators in app/Http/Requests/');
        $this->line('  6. php artisan permissions:sync');
        $this->line('  7. php artisan conventions:check');

        return self::SUCCESS;
    }

    // =========================================================================
    // STUBS
    // =========================================================================

    private function stubInterface(string $name): string
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\app\\Interfaces;

interface I{$name}Service
{
    public function index(array \$filter): array;
    public function store(array \$data, string \$actorId): mixed;
    public function edit(string \$id): mixed;
    public function update(string \$id, array \$data, string \$actorId): mixed;
    public function delete(string \$id): void;
    public function deleteSelected(array \$ids): int;
}
PHP;
    }

    private function stubService(string $name): string
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\app\\Services;

use App\\Exceptions\\NotFoundAppException;
use Modules\\{$name}\\app\\Interfaces\\I{$name}Service;

class {$name}Service implements I{$name}Service
{
    public function index(array \$filter): array
    {
        // TODO: implement index with filtering & pagination
        return ['items' => [], 'total' => 0, 'per_page' => 10, 'page' => 1, 'total_pages' => 0];
    }

    public function store(array \$data, string \$actorId): mixed
    {
        // TODO: implement store
        throw new \\RuntimeException('Not implemented');
    }

    public function edit(string \$id): mixed
    {
        // TODO: implement edit / find by id
        throw new NotFoundAppException('{$name} not found');
    }

    public function update(string \$id, array \$data, string \$actorId): mixed
    {
        // TODO: implement update
        throw new NotFoundAppException('{$name} not found');
    }

    public function delete(string \$id): void
    {
        // TODO: implement delete
        throw new NotFoundAppException('{$name} not found');
    }

    public function deleteSelected(array \$ids): int
    {
        // TODO: implement bulk delete
        return 0;
    }
}
PHP;
    }

    private function stubWebController(string $name, string $lower): string
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\app\\Http\\Controllers\\Web\\V1;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Modules\\{$name}\\app\\Interfaces\\I{$name}Service;

class {$name}Controller extends Controller
{
    public function __construct(
        private I{$name}Service \${$lower}Service,
    ) {}

    public function index(Request \$request)
    {
        \$filter = \$request->only(['q_name', 'q_status', 'q_page_size', 'page']);
        \$result = \$this->{$lower}Service->index(\$filter);
        return view('{$lower}-module::be.default.{$lower}.index', compact('result', 'filter'));
    }

    public function create()
    {
        return view('{$lower}-module::be.default.{$lower}.create');
    }

    public function store(Request \$request)
    {
        \$actorId = session('user_id');
        \$this->{$lower}Service->store(\$request->validate([
            'name' => 'required|string|max:255',
        ]), \$actorId);
        return redirect()->route('admin.v1.{$lower}.index')->with('success', '{$name} created successfully.');
    }

    public function edit(string \$id)
    {
        \$item = \$this->{$lower}Service->edit(\$id);
        return view('{$lower}-module::be.default.{$lower}.edit', compact('item'));
    }

    public function update(Request \$request, string \$id)
    {
        \$actorId = session('user_id');
        \$this->{$lower}Service->update(\$id, \$request->validate([
            'name' => 'required|string|max:255',
        ]), \$actorId);
        return redirect()->route('admin.v1.{$lower}.index')->with('success', '{$name} updated successfully.');
    }

    public function delete(string \$id)
    {
        \$this->{$lower}Service->delete(\$id);
        return redirect()->route('admin.v1.{$lower}.index')->with('success', '{$name} deleted.');
    }

    public function deleteSelected(Request \$request)
    {
        \$ids = \$request->input('selected', []);
        \$this->{$lower}Service->deleteSelected(\$ids);
        return redirect()->route('admin.v1.{$lower}.index')->with('success', 'Selected items deleted.');
    }
}
PHP;
    }

    private function stubApiController(string $name, string $lower): string
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\app\\Http\\Controllers\\Api\\V1;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use Modules\\{$name}\\app\\Interfaces\\I{$name}Service;

class {$name}Controller extends Controller
{
    public function __construct(
        private I{$name}Service \${$lower}Service,
    ) {}

    public function index(Request \$request): JsonResponse
    {
        \$filter = \$request->only(['q_name', 'q_status', 'q_page_size', 'page']);
        \$result = \$this->{$lower}Service->index(\$filter);
        return response()->json(['status' => 'success', 'data' => \$result]);
    }

    public function store(Request \$request): JsonResponse
    {
        \$actorId = \$request->attributes->get('jwt_user_id', '');
        \$item    = \$this->{$lower}Service->store(\$request->validate([
            'name' => 'required|string|max:255',
        ]), \$actorId);
        return response()->json(['status' => 'success', 'data' => \$item], 201);
    }

    public function show(string \$id): JsonResponse
    {
        \$item = \$this->{$lower}Service->edit(\$id);
        return response()->json(['status' => 'success', 'data' => \$item]);
    }

    public function update(Request \$request, string \$id): JsonResponse
    {
        \$actorId = \$request->attributes->get('jwt_user_id', '');
        \$item    = \$this->{$lower}Service->update(\$id, \$request->validate([
            'name' => 'required|string|max:255',
        ]), \$actorId);
        return response()->json(['status' => 'success', 'data' => \$item]);
    }

    public function delete(string \$id): JsonResponse
    {
        \$this->{$lower}Service->delete(\$id);
        return response()->json(['status' => 'success', 'message' => '{$name} deleted.']);
    }

    public function deleteSelected(Request \$request): JsonResponse
    {
        \$ids   = \$request->input('ids', []);
        \$count = \$this->{$lower}Service->deleteSelected(\$ids);
        return response()->json(['status' => 'success', 'deleted' => \$count]);
    }
}
PHP;
    }

    private function stubServiceProvider(string $name, string $lower, bool $webOnly, bool $apiOnly): string
    {
        $loadWeb = ! $apiOnly ? "\$this->loadRoutesFrom(module_path('{$name}', 'routes/web.php'));" : '';
        $loadApi = ! $webOnly ? "\$this->loadRoutesFrom(module_path('{$name}', 'routes/api.php'));" : '';

        return <<<PHP
<?php

namespace Modules\\{$name}\\app\\Providers;

use Illuminate\\Support\\ServiceProvider;
use Modules\\{$name}\\app\\Interfaces\\I{$name}Service;
use Modules\\{$name}\\app\\Services\\{$name}Service;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(
            I{$name}Service::class,
            {$name}Service::class,
        );
    }

    public function boot(): void
    {
        \$this->loadMigrationsFrom(module_path('{$name}', 'database/migrations'));
        \$this->loadViewsFrom(module_path('{$name}', 'resources/views'), '{$lower}-module');
        {$loadWeb}
        {$loadApi}
    }
}
PHP;
    }

    private function stubWebRoutes(string $name, string $lower): string
    {
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use Modules\\{$name}\\app\\Http\\Controllers\\Web\\V1\\{$name}Controller;

Route::middleware(['web', 'auth.app', 'authorize'])->prefix('admin/v1/{$lower}')->group(function () {
    Route::get('/',                   [{$name}Controller::class, 'index'])->name('admin.v1.{$lower}.index');
    Route::get('/create',             [{$name}Controller::class, 'create'])->name('admin.v1.{$lower}.create');
    Route::post('/store',             [{$name}Controller::class, 'store'])->name('admin.v1.{$lower}.store');
    Route::get('/{id}/edit',          [{$name}Controller::class, 'edit'])->name('admin.v1.{$lower}.edit');
    Route::put('/{id}/update',        [{$name}Controller::class, 'update'])->name('admin.v1.{$lower}.update');
    Route::delete('/{id}/delete',     [{$name}Controller::class, 'delete'])->name('admin.v1.{$lower}.delete');
    Route::post('/delete_selected',   [{$name}Controller::class, 'deleteSelected'])->name('admin.v1.{$lower}.delete_selected');
});
PHP;
    }

    private function stubApiRoutes(string $name, string $lower): string
    {
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use Modules\\{$name}\\app\\Http\\Controllers\\Api\\V1\\{$name}Controller;

Route::middleware(['auth.app'])->prefix('api/v1/{$lower}')->group(function () {
    Route::get('/',                   [{$name}Controller::class, 'index'])->name('api.v1.{$lower}.index');
    Route::post('/store',             [{$name}Controller::class, 'store'])->name('api.v1.{$lower}.store');
    Route::get('/{id}',               [{$name}Controller::class, 'show'])->name('api.v1.{$lower}.show');
    Route::put('/{id}/update',        [{$name}Controller::class, 'update'])->name('api.v1.{$lower}.update');
    Route::delete('/{id}/delete',     [{$name}Controller::class, 'delete'])->name('api.v1.{$lower}.delete');
    Route::post('/delete_selected',   [{$name}Controller::class, 'deleteSelected'])->name('api.v1.{$lower}.delete_selected');
});
PHP;
    }

    private function stubModuleJson(string $name): string
    {
        $lower = strtolower($name);

        return json_encode([
            'name' => $name,
            'alias' => $lower,
            'description' => "{$name} module",
            'keywords' => [],
            'priority' => 0,
            'providers' => [
                "Modules\\{$name}\\app\\Providers\\{$name}ServiceProvider",
            ],
            'files' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }

    private function stubTest(string $name, string $lower): string
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\Tests\\Feature;

use Tests\\TestCase;
use App\\Models\\Role;
use App\\Models\\User;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Facades\\Route;

class {$name}Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stub shared sidebar routes so blade layout does not throw
        if (! Route::has('admin.v1.dashboard.index')) {
            Route::get('/admin/v1/dashboard', fn () => 'stub')->name('admin.v1.dashboard.index');
        }
        if (! Route::has('admin.v1.components.index')) {
            Route::get('/admin/v1/components', fn () => 'stub')->name('admin.v1.components.index');
        }
        if (! Route::has('admin.v1.setting.index')) {
            Route::get('/admin/v1/setting', fn () => 'stub')->name('admin.v1.setting.index');
        }
    }

    private function actingAsAdmin(): User
    {
        \$role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['guard_name' => 'web', 'status' => 'Active']
        );
        \$user = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin.{$lower}@test.example',
            'password' => bcrypt('password'),
            'status'   => 'Active',
            'code'     => 'TST-001',
        ]);
        \$user->roles()->sync([\$role->id]);
        \$this->withSession(['user_id' => \$user->id]);
        return \$user;
    }

    public function test_{$lower}_index_loads(): void
    {
        \$this->actingAsAdmin();
        \$response = \$this->get('/admin/v1/{$lower}');
        \$response->assertOk();
    }

    public function test_{$lower}_create_page_loads(): void
    {
        \$this->actingAsAdmin();
        \$response = \$this->get('/admin/v1/{$lower}/create');
        \$response->assertOk();
    }
}
PHP;
    }

    private function stubViewIndex(string $name, string $lower): string
    {
        return <<<BLADE
@extends('layouts.be.default.main')
@section('title', '{$name}')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{$name} Management</h1>
</div>

<div class="tw-card p-0 overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-bold" style="color:var(--primary)">{$name} List</h2>
        <div class="btn-group btn-sm">
            <a href="{{ route('admin.v1.{$lower}.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-fw fa-plus"></i> Add Data
            </a>
            <button type="submit" form="selection"
                    formaction="{{ route('admin.v1.{$lower}.delete_selected') }}"
                    data-confirm="Delete selected {$lower}s?" class="btn btn-danger btn-sm">
                <i class="fas fa-fw fa-times"></i> Delete Selected
            </button>
        </div>
    </div>
    <div class="p-4" style="overflow-x:auto">
        <form id="selection" method="POST" action="{{ route('admin.v1.{$lower}.delete_selected') }}">
            @csrf
        </form>
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th width="2%"></th>
                    <th>#</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th width="12%">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse(\$result['items'] ?? [] as \$i => \$item)
                <tr>
                    <td>
                        <input type="checkbox" name="selected[]" value="{{ \$item['id'] }}" form="selection">
                    </td>
                    <td>{{ \$i + 1 }}</td>
                    <td>{{ \$item['name'] ?? '-' }}</td>
                    <td>
                        <span class="badge {{ (\$item['status'] ?? '') === 'Active' ? 'badge-success' : 'badge-secondary' }}">
                            {{ \$item['status'] ?? '-' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.v1.{$lower}.edit', \$item['id']) }}" class="btn btn-xs btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.v1.{$lower}.delete', \$item['id']) }}"
                              style="display:inline" data-confirm="Delete this {$lower}?">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-gray-400">No data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
BLADE;
    }

    private function stubViewCreate(string $name, string $lower): string
    {
        return <<<BLADE
@extends('layouts.be.default.main')
@section('title', 'Create {$name}')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Create {$name}</h1>
    <a href="{{ route('admin.v1.{$lower}.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left fa-fw"></i> Back
    </a>
</div>

<div class="tw-card">
    <form method="POST" action="{{ route('admin.v1.{$lower}.store') }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="name">Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ \$message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="status">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status"
                        class="form-control @error('status') is-invalid @enderror" required>
                    <option value="">-- Select --</option>
                    @foreach(['Active','Inactive'] as \$s)
                    <option value="{{ \$s }}" {{ old('status') === \$s ? 'selected' : '' }}>{{ \$s }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ \$message }}</div>@enderror
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.v1.{$lower}.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
BLADE;
    }

    private function stubViewEdit(string $name, string $lower): string
    {
        return <<<BLADE
@extends('layouts.be.default.main')
@section('title', 'Edit {$name}')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Edit {$name}</h1>
    <a href="{{ route('admin.v1.{$lower}.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left fa-fw"></i> Back
    </a>
</div>

<div class="tw-card">
    <form method="POST" action="{{ route('admin.v1.{$lower}.update', \$item->id ?? \$item['id']) }}">
        @csrf
        @method('PUT')
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="name">Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', \$item->name ?? \$item['name'] ?? '') }}" required>
                @error('name')<div class="invalid-feedback">{{ \$message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="status">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status"
                        class="form-control @error('status') is-invalid @enderror" required>
                    <option value="">-- Select --</option>
                    @foreach(['Active','Inactive'] as \$s)
                    <option value="{{ \$s }}"
                        {{ old('status', \$item->status ?? \$item['status'] ?? '') === \$s ? 'selected' : '' }}>
                        {{ \$s }}
                    </option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ \$message }}</div>@enderror
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.v1.{$lower}.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
BLADE;
    }
}
