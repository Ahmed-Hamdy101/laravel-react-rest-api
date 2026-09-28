<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Str; 
use Symfony\Component\Routing\Loader\Configurator\Routes;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // PERMISSION CHECK GATE
        Gate::define('view', function (User $user, $model) {
            // Get the raw class name string (e.g., "App\Models\User" or "User")
            $className = is_object($model) ? get_class($model) : $model;                    
            
            //  Extract  the model name (e.g., "User")
            $modelName = class_basename($className); 
            
            //  Pluralize it (e.g., "User" -> "Users")
            $pluralModel = Str::plural($modelName); 
            
            // Combine DB format (e.g., "View Users")
            $permissionNeeded = "View " . ucfirst($pluralModel);
            
            //  check against  user permissions
            return $user->hasPermission($permissionNeeded);
        });

         // define for editors
         Gate::define('edit', function (User $user, $model) {
            // Get the raw class name string (e.g., "App\Models\User" or "User")
            $className = is_object($model) ? get_class($model) : $model;                    
            
            // Extract  the model name (e.g., "User")
            $modelName = class_basename($className); 
            
            // Set Pluralize (e.g., "User" -> "Users")
            $pluralModel = Str::plural($modelName); 
            
            // Combine with DB format (e.g., "View Users")
            $permissionNeeded = "Edit " . ucfirst($pluralModel);
            
            //  check against  user permissions
            return $user->hasPermission($permissionNeeded);
        });

    }
}