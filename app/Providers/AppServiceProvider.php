<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use function mb_ltrim;
use function mb_trim;
use function shell_exec;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('git.version', function () {
            $version = mb_trim(shell_exec('git describe --tags --always 2>/dev/null') ?? '');

            if (empty($version)) {
                $version = '1.0.0';
            }

            return mb_ltrim($version, 'v');
        });
    }
}
