<?php

use Illuminate\Database\Seeder;

/**
 * Class CountriesSeeder
 */
class CountriesSeeder extends Seeder
{
    /**
     * Safely seeds countries table (doesn't fail when record exists).
     *
     * @return void
     */
    public function run()
    {
        $array = \SameerShelavale\PhpCountriesArray\CountriesArray::$countries;

        foreach ($array as $alpha2 => $info) {
            $data = [
                'name'            => $info['name'],
                'iso_alpha2_code' => $info['alpha2'],
                'iso_alpha3_code' => $info['alpha3'],
            ];

            $existing = DB::table('countries')
                ->where('name', $data['name'])
                ->first();

            if (!$existing) {
                DB::table('countries')->insert($data);
            } else {
                DB::table('countries')
                    ->where('iso_alpha2_code', $data['iso_alpha2_code'])
                    ->update($data);
            }
        }
    }
}
