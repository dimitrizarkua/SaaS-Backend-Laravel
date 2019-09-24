<?php

use Illuminate\Database\Seeder;

/**
 * Class AssessmentReportFormItemsSeeder
 */
class AssessmentReportFormItemsSeeder extends Seeder
{
    private $carpetAges = [
        '1-2',
        '2-4',
        '4-6',
        '6-8',
        '8-10',
        '10-12',
        '12-14',
        '15+',
        '20+',
        '25+',
        '30+',
        '35+',
        '40+',
    ];

    private $carpetTypes = [
        'Wool',
        'Nylon',
        'Acrylic',
        'Polypropylene',
        'Sisal',
        '80/20 Wool Nylon Blend',
        'Other',
    ];

    private $carpetConstructionTypes = [
        'Carpet Tiles',
        'Flocked (Flotex)',
        'Needle Punched',
        'Tufted (Jute)',
        'Tufted (Action Back)',
        'Tufted (Felt Back)',
        'Woven (Axminster)',
        'Woven (Wilton)',
        'Rubber Back',
        'Other',
    ];

    private $carpetFaceFibres = [
        'Cut & loop pile',
        'Cut pile',
        'High and low',
        'Loop pile',
        'Shag pile',
        'N/A',
    ];

    private $underlayTypes = [
        'Rubber',
        'Hair',
        'Jute',
        'Foam',
        'Dual Bond',
        'Cushion Pad',
        'Other',
    ];

    private $flooringTypes = [
        'contents'  => [
            'Carpet',
            'Direct Stick Carpet',
            'Carpet Tiles',
            'Floating Floor - Bamboo',
        ],
        'structure' => [
            'Concrete',
            'Cork',
            'Floating Floor - Hardwood',
            'Tiles',
            'Timber',
            'Vinyl - Direct Stick',
            'Vinyl - Loose Laid',
            'Floating Floor - Engineered',
            'Floating Floor - Laminate',
            'Synthetic Turf',
            'Pavers',
            'Bitumen',
            'Other',
        ],
    ];

    //todo implement seeder when it becomes clear with which flooring types associate current subtypes
    private $flooringSubtypes = [
        'Concrete',
        'Timber - Hardwood',
        'Timber - Particleboard',
    ];

    private $nonRestorableReason = [
        'Burn Marks',
        'Category 3',
        'Cellulosic Browning',
        'Delamination',
        'Furniture Stains',
        'Health Concerns',
        'Insured Cutout',
        'Mould',
        'Odour',
        'Part of Open Living',
        'Shrinkage',
        'Stains',
        'Starting to Bubble',
        'Swollen',
        'Water Marks',
    ];

    //todo implement seeder when according table will be created
    private $surfaceTypes = [
        'Floor - Carpet',
        'Floor - Underlay',
        'Wall - Skirting Board',
        'Wall - Plaster',
        'Ceiling - Plaster',
        'Wall - Timber',
        'Ceiling - Timber',
        'Framing - Stud',
        'Framing - Base Plate',
        'Sub-Floor - Particleboard',
        'Sub-Floor - Plywood',
        'Framing - Floor Joist',
        'Floor - Tiles',
        'Sub-Floor - Concrete',
        'Sub-Floor - Hardwood',
        'Wall - Rendered',
        'Floor - Cork',
        'Floor - Parquetry',
        'Floor - Vinyl',
        'Wall - Architrave',
        'Wall - Masonry',
        'Framing - Timber Stumps',
        'Floor - Hardwood',
        'Framing - Roof Truss',
        'Ceiling - Stramit',
        'Cabinetry - Kickboard',
        'Soil',
        'Other',
    ];

    /**
     * @return void
     */
    public function run()
    {
        $this->seedAssessmentReportFormItemTable($this->carpetAges, 'carpet_ages');
        $this->seedAssessmentReportFormItemTable($this->carpetTypes, 'carpet_types');
        $this->seedAssessmentReportFormItemTable($this->carpetConstructionTypes, 'carpet_construction_types');
        $this->seedAssessmentReportFormItemTable($this->carpetFaceFibres, 'carpet_face_fibres');
        $this->seedAssessmentReportFormItemTable($this->underlayTypes, 'underlay_types');
        $this->seedAssessmentReportFormItemTable($this->nonRestorableReason, 'non_restorable_reasons');
        $this->seedFlooringType();
    }

    /**
     * Seed a table.
     *
     * @param array  $properties
     * @param string $tableName
     *
     * @return void
     */
    private function seedAssessmentReportFormItemTable(array $properties, string $tableName)
    {
        foreach ($properties as $property) {
            $existing = DB::table($tableName)
                ->where('name', $property)
                ->first();

            if (!$existing) {
                DB::table($tableName)
                    ->insert([
                        'name' => $property,
                    ]);
            }
        }
    }

    /**
     * Seed the flooring_types table.
     *
     * @return void
     */
    private function seedFlooringType()
    {
        foreach ($this->flooringTypes as $type) {
            foreach ($type as $name) {
                $existing = DB::table('flooring_types')
                    ->where('name', $name)
                    ->first();

                if (!$existing) {
                    DB::table('flooring_types')
                        ->insert([
                            'name' => $name,
                        ]);
                }
            }
        }
    }
}
