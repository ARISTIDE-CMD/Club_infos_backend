<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use App\Models\User; // Ajoutez cette ligne pour importer le modèle User
use App\Models\Project;
use App\Observers\ProjectObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Définir la Gate 'is-admin'
        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin';
        });

        // Définir la Gate 'is-student'
        Gate::define('is-student', function (User $user) {
            return $user->role === 'student';
        });

        // Chargement des routes API
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
        Project::observe(ProjectObserver::class);

        Schema::defaultStringLength(191);
    }
}
