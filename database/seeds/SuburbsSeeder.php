<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 * Class SuburbsSeeder
 */
class SuburbsSeeder extends Seeder
{
    /**
     * Safely seeds suburbs table (doesn't fail when record exists).
     *
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        $this->call(StatesSeeder::class);
        $this->call(LocationsSeeder::class);

        $csv = Reader::createFromPath(database_path('misc/suburbs.csv'), 'r');
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);
        foreach ($records as $record) {
            $state = DB::table('states')
                ->where('code', $record['state_code'])
                ->first();

            $data = [
                'name'     => $record['suburb_name'],
                'postcode' => $record['postcode'],
                'state_id' => $state->id,
            ];

            $existing = DB::table('suburbs')
                ->where($data)
                ->first();

            if (!$existing) {
                DB::table('suburbs')->insert($data);
            }

            $suburb = DB::table('suburbs')
                ->where('postcode', $data['postcode'])
                ->first();

            $location = DB::table('locations')
                ->where('code', $record['location_code'])
                ->first();

            if (!$location) {
                continue;
            }

            $pivotData = [
                'location_id' => $location->id,
                'suburb_id'   => $suburb->id,
            ];

            $pivotRecord = DB::table('location_suburb')
                ->where($pivotData)
                ->first();

            if (!$pivotRecord) {
                DB::table('location_suburb')->insert($pivotData);
            }
        }
    }
}
