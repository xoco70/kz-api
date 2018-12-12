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

    protected function seedTournamentLevels()
    {
        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder']);
    }

    protected function seedGrades()
    {
        Artisan::call('db:seed', ['--class' => 'GradeSeeder']);
    }

//    protected function seedCountries()
//    {
//        Artisan::call('db:seed', ['--class' => 'CountriesSeeder']);
//    }

    /**
     * Seed basic elements for tournament creation
     */
    protected function seedBasicElements()
    {
        $this->createSuperUser();
        $this->seedCategories();
        $this->seedTournamentLevels();
        $this->seedGrades();
//        $this->seedCountries();
    }


}