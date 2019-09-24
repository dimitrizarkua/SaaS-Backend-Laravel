<?php

use Illuminate\Database\Seeder;

/**
 * Class JobContactAssignmentTypesSeeder
 */
class JobContactAssignmentTypesSeeder extends Seeder
{
    private $types = [
        [
            'name'      => 'Site Contact',
            'is_unique' => true,
        ],
        [
            'name'      => 'Customer',
            'is_unique' => false,
        ],
        [
            'name'      => 'Loss Adjustor',
            'is_unique' => false,
        ],
        [
            'name'      => 'Broker',
            'is_unique' => false,
        ],
        [
            'name'      => 'Referrer',
            'is_unique' => false,
        ],
    ];

    /**
     * Seed the contact types, categories and statuses.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->types as $type) {
            $existing = DB::table('job_contact_assignment_types')
                ->where('name', $type['name'])
                ->first();

            if (!$existing) {
                DB::table('job_contact_assignment_types')->insert([
                    'name'      => $type['name'],
                    'is_unique' => $type['is_unique'],
                ]);
            } else {
                DB::table('job_contact_assignment_types')->where('name', $type['name'])
                    ->update([
                        'is_unique' => $type['is_unique'],
                    ]);
            }
        }
    }
}
