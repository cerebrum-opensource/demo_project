<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('manageable_fields')->insert([[
            'type' => 'county',
            'name' => 'South America',
            'value' => 'south_america',
        ],
        [
            'type' => 'county',
            'name' => 'North America',
            'value' => 'north_america',
        ],
        [
            'type' => 'language',
            'name' => 'English',
            'value' => 'eng',
        ],
        [
            'type' => 'language',
            'name' => 'Spanish',
            'value' => 'spn',
        ],
        [
            'type' => 'patient_concern',
            'name' => 'Alcohol',
            'value' => 'alcohol',
        ],
        [
            'type' => 'patient_concern',
            'name' => 'Chronic Pain',
            'value' => 'chronic_pain',
        ],
        [
            'type' => 'lives_with',
            'name' => 'Family',
            'value' => 'family',
        ],
        [
            'type' => 'lives_with',
            'name' => 'Caregivers',
            'value' => 'caregivers',
        ],
        [
            'type' => 'lives_with',
            'name' => 'Roommate/Friends',
            'value' => 'roommate_or_friends',
        ],
        [
            'type' => 'lives_with',
            'name' => 'House',
            'value' => 'house',
        ],
        [
            'type' => 'lives_with',
            'name' => 'B&C',
            'value' => 'b_and_c',
        ],
        [
            'type' => 'lives_with',
            'name' => 'Assisted Living',
            'value' => 'assisted_living',
        ],
        [
            'type' => 'lives_with',
            'name' => 'Homeless',
            'value' => 'homeless',
        ],
        [
            'type' => 'lives_with',
            'name' => 'Sober living',
            'value' => 'sober_living',
        ],
        [
            'type' => 'lives_with',
            'name' => 'Residential treatment center',
            'value' => 'residential_treatment_center',
        ],
        [
            'type' => 'lives_with',
            'name' => 'Alone',
            'value' => 'alone',
        ],
        [
            'type' => 'patient_referal',
            'name' => 'Insurance Company',
            'value' => 'referred_by_insurance_company',
        ],
        [
            'type' => 'patient_referal',
            'name' => 'Family',
            'value' => 'refered_by_family',
        ]]);

		DB::table('contract_payers')->insert([[
            'name' => 'John Smith',
            'address' => 'Wilington Town, America',
            'confirmation' => '1',
            'start_date' => '2016-09-09',
            'end_date' => '2019-09-09',
            'contact_name' => 'Milky Hassan',
            'contact_phone' => '222-989-232',
            'contact_title' => 'Miss',
            'contact_email' => 'mily@gmail.com',
            'contact_fax' => 'MLY-223',
        ]]);		

        DB::table('insurances')->insert([[
            'name' => 'Jack Ching',
            'address_line1' => 'Wilington Town, South America',
            'address_line2' => 'Mid Town',
            'city' => 'Standford',
            'zip' => 'FYH-224',
            'contact_name' => 'Milky Hassan',
            'contact_phone' => '222-989-232',
            'contact_email' => 'mily@gmail.com'
        ],[
            'name' => 'Sam Hunger',
            'address_line1' => 'Wilington Town, South America',
            'address_line2' => 'Mid Town',
            'city' => 'Chicago',
            'zip' => 'FYH-276',
            'contact_name' => 'Stic Zing',
            'contact_phone' => '222-465-232',
            'contact_email' => 'zing@gmail.com'
        ]]);
    }
}
