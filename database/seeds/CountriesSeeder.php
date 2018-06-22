<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Webpatser\Countries\Countries;

class CountriesSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return  void
     */
    public function run()
    {
        $this->command->info('Countries seeding!');
        //Empty the countries table
        DB::table(config('countries.table_name'))->delete();

        //Get all of the countries
        $countries = new Countries();

        $countries = $countries->getList();

        //TODO Refactor it
        foreach ($countries as $countryId => $country){
            DB::table(config('countries.table_name'))->insert(array(
                'id' => $countryId,
                'capital' => $country['capital'] ?? null,
                'citizenship' => $country['citizenship'] ?? null,
                'country_code' => $country['country_code'] ?? null,
                'currency' => $country['currency'] ?? null,
                'currency_code' => $country['currency_code'] ?? null,
                'currency_sub_unit' => $country['currency_sub_unit'] ?? null,
                'full_name' => $country['full_name'] ?? null,
                'iso_3166_2' => $country['iso_3166_2'] ?? null,
                'iso_3166_3' => $country['iso_3166_3'] ?? null,
                'name' => $country['name'] ?? null,
                'region_code' => $country['region_code'] ?? null,
                'sub_region_code' => $country['sub_region_code'] ?? null,
                'eea' => $country['eea'] ?? null,
                'calling_code' => $country['calling_code'] ?? null,
                'currency_symbol' => $country['currency_symbol'] ?? null,
                'flag' => $country['flag'] ?? null,
            ));
        }

    }
}
