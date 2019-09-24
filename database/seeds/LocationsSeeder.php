<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 * Class LocationsSeeder
 */
class LocationsSeeder extends Seeder
{
    /**
     * Safely seeds locations table (doesn't fail when record exists).
     *
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        $csv = Reader::createFromPath(database_path('misc/locations.csv'), 'r');
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);
        foreach ($records as $record) {
            $data = [
                'name'      => $record['Name'],
                'code'      => $record['Code'],
                'tz_offset' => $record['tz_offset'],
            ];

            $existing = DB::table('locations')
                ->where('code', $data['code'])
                ->first();

            if (!$existing) {
                DB::table('locations')->insert($data);
            } else {
                DB::table('locations')
                    ->where('code', $data['code'])
                    ->update($data);
            }
        }
    }
}
