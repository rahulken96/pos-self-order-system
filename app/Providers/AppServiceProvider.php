<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use App\Extensions\SupabaseStorageAdapter;
use League\Flysystem\Filesystem as Flysystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

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
        Vite::prefetch(concurrency: 3);

        Storage::extend('supabase', function ($app, $config) {
            $adapter = new SupabaseStorageAdapter(
                $config['url'],
                $config['key'],
                $config['bucket']
            );

            return new FilesystemAdapter(
                new Flysystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
