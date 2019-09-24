<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 * Class TagsSeeder
 */
class TagsSeeder extends Seeder
{
    /**
     * Convenience method that convert hex number to its decimal representation.
     * This methods also remove # character in source value if present.
     *
     * @param string $value
     *
     * @return int
     */
    private function hexToInt(string $value): int
    {
        $value = preg_replace('/#/', '', $value);

        return hexdec($value);
    }

    /**
     * @param string $filePath      Full path to CSV file.
     * @param string $type          Tags type.
     * @param string $tagNameColumn Column name in CSV file that contains tag name.
     *
     * @throws \League\Csv\Exception
     */
    private function parseTags(string $filePath, string $type, string $tagNameColumn): void
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);
        foreach ($records as $record) {
            $tag = DB::table('tags')
                ->where('type', $type)
                ->whereRaw(sprintf('LOWER(name) = \'%s\'', strtolower(trim($record[$tagNameColumn]))))
                ->first();

            $data = [
                'name'     => $record[$tagNameColumn],
                'type'     => $type,
                'is_alert' => $record['Is Alert'] == '1',
                'color'    => $this->hexToInt($record['Hex Color']),
            ];

            if (!$tag) {
                DB::table('tags')->insert($data);
            } else {
                DB::table('tags')
                    ->where('id', $tag->id)
                    ->update($data);
            }
        }
    }

    /**
     * Safely seeds tags table (doesn't fail when record exists).
     *
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        // JOB tags

        $this->parseTags(database_path('misc/jobs_tags.csv'), 'job', 'JOB TAG Name');
        $this->parseTags(database_path('misc/contacts_tags.csv'), 'contact', 'CONTACT TAG Name');
    }
}
