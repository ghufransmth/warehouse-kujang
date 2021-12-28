<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {

        $this->registerPolicies();
        $permissions = \App\Models\Permission::all();
        foreach($permissions as $permission) {
            Gate::define($permission->slug, function($user) use ($permission) {
                $return = false;
                foreach ($permission->role as $role) {
                    $return = $user->hasRole($role->name);
                    if($return) break;
                }
                return $return;
            });
        }
    }
}
