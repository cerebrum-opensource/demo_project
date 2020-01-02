<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AddStaticQuestion extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
     
    public function run()
    {
        DB::table('questions')->insert([
        	[
	            'description' => "How confident is patient that he can achieve goals?",
	            'metric_id' => 59,
	            'question_type' => '1',
	            'no_of_visits' => '0',
	            'status' => '1',
	            'created_at' => now(),
	            'updated_at' => now()
        	],
        	[
	            'description' => 'Is patient priority same as care team?',
	            'metric_id' => 58,
	            'question_type' => '1',
	            'no_of_visits' => '0',
	            'status' => '1',
	            'created_at' => now(),
	            'updated_at' => now()
        	],
        	[
	            'description' => 'Has patient been admitted to hospital in last month?',
	            'metric_id' => 58,
	            'question_type' => '2',
	            'no_of_visits' => '1',
	            'status' => '1',
	            'created_at' => now(),
	            'updated_at' => now()
        	],
        	[
	            'description' => 'Has patient been to emergency room in last month?',
	            'metric_id' => 58,
	            'question_type' => '2',
	            'status' => '1',
	            'no_of_visits' => '1',
	            'created_at' => now(),
	            'updated_at' => now()
        	],
        	[
	            'description' => 'Total number of active medication?',
	            'metric_id' => 61,
	            'question_type' => '2',
	            'status' => '1',
	            'no_of_visits' => '0',
	            'created_at' => now(),
	            'updated_at' => now()
        	],
        	[
	            'description' => 'Are patient diagnosed symptoms improving?',
	            'metric_id' => 58,
	            'question_type' => '2',
	            'status' => '1',
	            'no_of_visits' => '0',
	            'created_at' => now(),
	            'updated_at' => Carbon::now()
        	],
        	[
	            'description' => 'Risk Score?',
	            'metric_id' => 59,
	            'question_type' => '2',
	            'status' => '1',
	            'no_of_visits' => '0',
	            'created_at' => now(),
	            'updated_at' => now()
        	]
        ]);
    }
}
