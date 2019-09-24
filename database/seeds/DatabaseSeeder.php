<?php

use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ContactsSeeder::class);
        $this->call(TagsSeeder::class);
        $this->call(SuburbsSeeder::class);
        $this->call(JobContactAssignmentTypesSeeder::class);
        $this->call(TaxRatesSeeder::class);
        $this->call(OperationsSeeder::class);
        $this->call(SiteSurveyQuestionsSeeder::class);
        $this->call(EquipmentCategoriesSeeder::class);
        $this->call(AssessmentReportFormItemsSeeder::class);
        $this->call(AccountTypeGroupsSeeder::class);
        $this->call(ImportLocationAndContactSeeder::class);
        $this->call(UserRolesPermissionsSeeder::class);
    }
}
