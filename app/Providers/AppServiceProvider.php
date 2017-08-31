<?php

namespace App\Providers;

use DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        DB::listen(function ($query) {
            //Log::info($query->sql);
            //Log::info($query->bindings);
            //Log::info($query->time);
            //$query->sql
            //$query->bindings
            //$query->time
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

}
