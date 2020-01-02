<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        
        DB::table('states')->insert([[
            'abbreviation' => 'AL',
            'full_name' => 'Alabama',
        ],
        [
            'abbreviation' => 'AK',
            'full_name' => 'Alaska',
        ],
        [
            'abbreviation' => 'AZ',
            'full_name' => 'Arizona',
        ],
        [
            'abbreviation' => 'AR',
            'full_name' => 'Arkansas',
        ],
        [
            'abbreviation' => 'CA',
            'full_name' => 'California',
        ],
        [
            'abbreviation' => 'CO',
            'full_name' => 'Colorado',
        ],
        [
            'abbreviation' => 'CT',
            'full_name' => 'Connecticut',
        ],
        [
            'abbreviation' => 'DE',
            'full_name' => 'Delaware',
        ],
        [
            'abbreviation' => 'DC',
            'full_name' => 'District of Columbia',
        ],
        [
            'abbreviation' => 'FL',
            'full_name' => 'Florida',
        ],
        [
            'abbreviation' => 'GA',
            'full_name' => 'Georgia',
        ],
        [
            'abbreviation' => 'HI',
            'full_name' => 'Hawaii',
        ],
        [
            'abbreviation' => 'IL',
            'full_name' => 'Illinois',
        ],
        [
            'abbreviation' => 'IN',
            'full_name' => 'Indiana',
        ],
        [
            'abbreviation' => 'KS',
            'full_name' => 'Kansas',
        ],
        [
            'abbreviation' => 'KY',
            'full_name' => 'Kentucky',
        ],
        [
            'abbreviation' => 'LA',
            'full_name' => 'Louisiana',
        ],
        [
            'abbreviation' => 'ME',
            'full_name' => 'Maine',
        ],
        [
            'abbreviation' => 'MD',
            'full_name' => 'Maryland',
        ],
        [
            'abbreviation' => 'MA',
            'full_name' => 'Massachusetts',
        ],
        [
            'abbreviation' => 'MI',
            'full_name' => 'Michigan',
        ],
        [
            'abbreviation' => 'MN',
            'full_name' => 'Minnesota',
        ],
        [
            'abbreviation' => 'MS',
            'full_name' => 'Mississippi',
        ],
        [
            'abbreviation' => 'MO',
            'full_name' => 'Missouri',
        ],
        [
            'abbreviation' => 'MT',
            'full_name' => 'Montana',
        ],
        [
            'abbreviation' => 'NE',
            'full_name' => 'Nebraska',
        ],
        [
            'abbreviation' => 'NV',
            'full_name' => 'Nevada',
        ],
        [
            'abbreviation' => 'NH',
            'full_name' => 'New Hampshire',
        ],
        [
            'abbreviation' => 'NJ',
            'full_name' => 'New Jersey',
        ],
        [
            'abbreviation' => 'NM',
            'full_name' => 'New Mexico',
        ],
        [
            'abbreviation' => 'NY',
            'full_name' => 'New York',
        ],
        [
            'abbreviation' => 'NC',
            'full_name' => 'North Carolina',
        ],
        [
            'abbreviation' => 'ND',
            'full_name' => 'North Dakota',
        ],
        [
            'abbreviation' => 'OH',
            'full_name' => 'Ohio',
        ],
        [
            'abbreviation' => 'OK',
            'full_name' => 'Oklahoma',
        ],
        [
            'abbreviation' => 'OR',
            'full_name' => 'Oregon',
        ],
        [
            'abbreviation' => 'PA',
            'full_name' => 'Pennsylvania',
        ],
        [
            'abbreviation' => 'RI',
            'full_name' => 'Rhode Island',
        ],
        [
            'abbreviation' => 'SC',
            'full_name' => 'South Carolina',
        ],
        [
            'abbreviation' => 'SD',
            'full_name' => 'South Dakota',
        ],
        [
            'abbreviation' => 'TN',
            'full_name' => 'Tennessee',
        ],
        [
            'abbreviation' => 'TX',
            'full_name' => 'Texas',
        ],
        [
            'abbreviation' => 'UT',
            'full_name' => 'Utah',
        ],
        [
            'abbreviation' => 'VT',
            'full_name' => 'Vermont',
        ],
        [
            'abbreviation' => 'VA',
            'full_name' => 'Virginia',
        ],
        [
            'abbreviation' => 'WA',
            'full_name' => 'Washingtons',
        ],
        [
            'abbreviation' => 'WV',
            'full_name' => 'West Virginia',
        ],
        [
            'abbreviation' => 'WI',
            'full_name' => 'Wisconsin',
        ],
        [
            'abbreviation' => 'WY',
            'full_name' => 'Wyoming',
        ]]);
    }
}
