<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 * Class StatesSeeder
 */
class StatesSeeder extends Seeder
{
    /**
     * Safely seeds states table (doesn't fail when record exists).
     *
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        $this->call(CountriesSeeder::class);

        $csv = Reader::createFromPath(database_path('misc/states.csv'), 'r');
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);
        foreach ($records as $record) {
            $country = DB::table('countries')
                ->where('iso_alpha2_code', $record['country_iso_code'])
                ->first();

            $data = [
                'name'       => $record['name'],
                'code'       => $record['code'],
                'country_id' => $country->id,
            ];

            $existing = DB::table('states')
                ->where('code', $data['code'])
                ->first();

            if (!$existing) {
                DB::table('states')->insert($data);
            } else {
                DB::table('states')
                    ->where('code', $data['code'])
                    ->update($data);
            }
        }
    }
}
