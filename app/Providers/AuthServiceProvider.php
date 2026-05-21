<?php
// app/Providers/AuthServiceProvider.php
namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('kyc.review', function (User $user): bool {
            return (bool) $user->is_admin;
        });
    }
}
