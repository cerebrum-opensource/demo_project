<?php

use Illuminate\Database\Seeder;

class CareplanAssessmentPurpose extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('manageable_fields')->insert([
            [
                'name' => 'Educational',
                'type' => 'careplan_assessment_purpose',
                'value' => 'educational',
                'description' => '',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Instrumental',
                'type' => 'careplan_assessment_purpose',
                'value' => 'instrumental',
                'description' => '',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'In Person',
                'type' => 'careplan_assessment_via',
                'value' => 'in_person',
                'description' => '',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'In Phone',
                'type' => 'careplan_assessment_via',
                'value' => 'in_phone',
                'description' => '',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'In Person with interpretor',
                'type' => 'careplan_assessment_via',
                'value' => 'in_person_with_interpretor',
                'description' => '',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
