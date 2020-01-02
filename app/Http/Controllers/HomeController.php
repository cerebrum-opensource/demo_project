<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth',['except' => 'permision']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function permision()
    {
        $user=User::create([
            'name' => 'Admin',
            'email' => 'admin@yopmail.com',
            'password' => bcrypt('Welcome@123'),
            'status' => 1,
            'password_expiry' => \Carbon\Carbon::now()->addDays(15)
        ]);

        

        \App\Models\LastPassword::create(['password' =>$user->password,'type_id'=> $user->id,'type'=>'user']);

        $role = Role::create(['name' => 'Admin']);
       // $permission = Permission::create(['name' => 'Add Case Manager']);
      //  $role->givePermissionTo($permission);

        $user->assignRole($role);
        $role2 = Role::create(['name' => 'CM']);
        $role3 = Role::create(['name' => 'MD']);
        $role4 = Role::create(['name' => 'CHW']);
        $permission2 = Permission::create(['name' => 'List All Referred Patients']);
        $permission3 = Permission::create(['name' => 'Refer New Patient']);
        $permission4 = Permission::create(['name' => 'Edit Referred Patient']);
        $role2->givePermissionTo($permission2);
        $role2->givePermissionTo($permission3);
        $role2->givePermissionTo($permission4);

        //  echo "I am here";
        //  die();
        //  return view('home');
    }
}
