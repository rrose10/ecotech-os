<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Opens every read-only (GET) page of the site, once for each kind of login
 * (super admin, admin, hr, employee), using the real data in the database,
 * and reports any page that crashes. It never writes anything.
 */
class SmokeTest extends Command
{
    protected $signature = 'ecotech:smoke {--roles=super_admin,admin,hr,employee : roles to test}';

    protected $description = 'Open every GET page for each role and report errors (read-only)';

    private const SKIP = ['logout', 'storage', 'up', 'sanctum', 'verify-email', 'reset-password', 'confirm-password'];

    public function handle(): int
    {
        $failures = 0;
        $checked = 0;
        $kernel = app(Kernel::class);

        foreach (explode(',', $this->option('roles')) as $role) {
            $user = User::where('role', trim($role))->orderBy('id')->first();

            if (! $user) {
                $this->warn("[skip] no login with role '{$role}'");
                continue;
            }

            // Pretend the password is already changed, in memory only.
            $user->must_change_password = false;

            $this->newLine();
            $this->info("== {$role} ({$user->email}) ==");

            foreach (Route::getRoutes() as $route) {
                if (! in_array('GET', $route->methods(), true) || $this->skipped($route)) {
                    continue;
                }

                $uri = $this->fill($route);
                if ($uri === null) {
                    continue; // no data to open this page with
                }

                $checked++;
                $result = $this->open($kernel, $user, $uri);

                if ($result['status'] >= 500) {
                    $failures++;
                    $this->error(sprintf('  %d  %s', $result['status'], $uri));
                    $this->line('      ' . $result['error']);
                } elseif ($result['status'] === 404) {
                    $this->warn(sprintf('  404  %s', $uri));
                } else {
                    $this->line(sprintf('  %d  %s', $result['status'], $uri));
                }
            }
        }

        $this->newLine();
        $this->line("Checked {$checked} pages. Crashes: {$failures}");

        return $failures ? self::FAILURE : self::SUCCESS;
    }

    private function skipped(RoutingRoute $route): bool
    {
        $uri = $route->uri();

        if (str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum')) {
            return true;
        }

        foreach (self::SKIP as $word) {
            if (str_contains($uri, $word)) {
                return true;
            }
        }

        return false;
    }

    /** Replace {params} with real ids from the database; null if there is none. */
    private function fill(RoutingRoute $route): ?string
    {
        $uri = $route->uri();

        if (! str_contains($uri, '{')) {
            return '/' . ltrim($uri, '/');
        }

        $models = [];
        foreach ($route->signatureParameters(['subClass' => UrlRoutable::class]) as $p) {
            $models[$p->getName()] = $p->getType()?->getName();
        }

        foreach ($route->parameterNames() as $name) {
            $class = $models[$name] ?? null;
            if (! $class) {
                return null;
            }

            $record = $class::query()->orderBy((new $class)->getKeyName())->first();
            if (! $record) {
                return null;
            }

            $uri = preg_replace('/\{' . preg_quote($name, '/') . '\??\}/', (string) $record->getRouteKey(), $uri);
        }

        return '/' . ltrim($uri, '/');
    }

    private function open(Kernel $kernel, User $user, string $uri): array
    {
        try {
            Auth::guard('web')->setUser($user);
            $response = $kernel->handle(Request::create($uri, 'GET'));

            // Render streamed downloads (CSV) too, so crashes inside them show up.
            if (method_exists($response, 'sendContent') && ! method_exists($response, 'getContent')) {
                ob_start();
                $response->sendContent();
                ob_end_clean();
            }

            $error = '';
            if ($response->getStatusCode() >= 500) {
                $e = $response->exception ?? null;
                $error = $e instanceof Throwable
                    ? get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine()
                    : 'server error';
            }

            return ['status' => $response->getStatusCode(), 'error' => $error];
        } catch (Throwable $e) {
            return [
                'status' => 500,
                'error' => get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine(),
            ];
        }
    }
}
