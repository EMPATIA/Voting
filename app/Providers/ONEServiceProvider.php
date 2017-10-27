<?php

namespace App\Providers;

use App\One\One;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Form;
use Session;

class ONEServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        App::bind('one', function() {
            return new One();
        });
    }
}
