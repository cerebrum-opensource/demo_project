<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AddNewRoles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
        	[
	            'name' => 'CHW',
	            'guard_name' => 'web',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	],
        	[
	            'name' => 'MD',
	            'guard_name' => 'web',
	            'created_at' => Carbon::now(),
	            'updated_at' => Carbon::now()
        	]
        ]);
    }
}
