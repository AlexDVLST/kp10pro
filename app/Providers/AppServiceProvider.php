<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //fix for MySQL older than the 5.7.7
        Schema::defaultStringLength(191);

        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            //12 is max size for phone column in DB
            return strlen(preg_replace('/[^0-9]/', '', $value)) <= 12;
        });
        //Validate domain name without protocol
        Validator::extend('domain', function ($attribute, $value, $parameters, $validator) {
            return filter_var($value, FILTER_VALIDATE_DOMAIN);
        });
        
        //Carbon set localte
        setlocale(LC_TIME, 'ru_RU.UTF-8');
        
        //Change localization for Faker
        $this->app->singleton(\Faker\Generator::class, function () {
            return \Faker\Factory::create('ru_RU');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
