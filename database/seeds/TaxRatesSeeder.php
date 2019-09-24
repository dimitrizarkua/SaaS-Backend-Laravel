<?php

use Illuminate\Database\Seeder;

/**
 * Class ContactsSeeder
 */
class TaxRatesSeeder extends Seeder
{
    private $data = [
        [
            'name' => 'GST on Income',
            'rate' => 0.1,
        ],
        [
            'name' => 'GST on Expenses',
            'rate' => 0.1,
        ],
        [
            'name' => 'GST Free Income',
            'rate' => 0,
        ],
        [
            'name' => 'GST Free Expenses',
            'rate' => 0,
        ],
        [
            'name' => 'BAS Excluded',
            'rate' => 0,
        ],
    ];

    /**
     * Seed the contact types, categories and statuses.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $item) {
            DB::table('tax_rates')->insert($item);
        }
    }
}
