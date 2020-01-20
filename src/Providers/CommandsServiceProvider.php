<?php

namespace Commands\Relations\Providers;

use Illuminate\Support\ServiceProvider;
use Commands\Relations\{RelationManyToManyCommand, RelationOneToManyCommand, RelationOneToOneCommand};

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() 
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RelationManyToManyCommand::class,
                RelationOneToManyCommand::class,
                RelationOneToOneCommand::class
            ]);
        }
    }    
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() 
    {
        //
    }
}
