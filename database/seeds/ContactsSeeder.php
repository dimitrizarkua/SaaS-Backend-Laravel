<?php

use App\Components\Contacts\Enums\ContactCategoryTypes;
use Illuminate\Database\Seeder;

/**
 * Class ContactsSeeder
 */
class ContactsSeeder extends Seeder
{
    /**
     * Seed the contact types, categories and statuses.
     *
     * @return void
     */
    public function run()
    {
        $contactCategories = ContactCategoryTypes::values();

        foreach ($contactCategories as $category) {
            DB::table('contact_categories')->insert([
                'name' => ucfirst(str_ireplace('_', ' ', $category)),
                'type' => strtolower($category),
            ]);
        }
    }
}
