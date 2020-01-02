<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AddFlagAndMetrics extends Seeder
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
	            'name' => 'Green',
	            'type' => 'flag',
	            'value' => 'green',
	            'description' => '',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	],
        	[
	            'name' => 'Yellow',
	            'type' => 'flag',
	            'value' => 'yellow',
	            'description' => '',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	],
        	[
	            'name' => 'Red',
	            'type' => 'flag',
	            'value' => 'red',
	            'description' => '',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	],
        	[
	            'name' => 'Binary',
	            'type' => 'metric',
	            'value' => '[Yes,No]',
	            'description' => 'Yes or No',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	],
        	[
	            'name' => 'Scale',
	            'type' => 'metric',
	            'value' => '[Low,Medium,High]',
	            'description' => 'Low or Medium or High',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	],
        	[
	            'name' => 'Level',
	            'type' => 'metric',
	            'value' => '[1,2,3,4,5]',
	            'description' => '1,2,3,4,5',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	],
        	[
	            'name' => 'Note',
	            'type' => 'metric',
	            'value' => '[free_text]',
	            'description' => 'Free Text',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	]
        ]);
    }
}
