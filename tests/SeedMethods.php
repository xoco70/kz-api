<?php

use Illuminate\Support\Facades\Artisan;

trait SeedMethods
{
    protected function createSuperUser()
    {
        return factory(App\User::class)->create([
            'email' => 'superuser@kendozone.dev',
            'password' => app('hash')->make('superuser')
        ]);
    }

    protected function seedCategories()
    {
        Artisan::call('db:seed', ['--class' => 'CategorySeeder']);
    }


}