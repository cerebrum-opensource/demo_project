<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\CanResetPassword;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'password_expiry','image','confirmation_code','status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\MailResetPasswordNotification($token));
    }

    public static function getPatientCareTeamList()
    {
        $users = [];

        $users['chw_users'] = User::whereHas('roles', function ($query) {
            $query->where('name', '=', COMMUNITYHEALTHWORKER);}
            )->where('status', 1)
            ->orderBy('name','asc')
            ->get()->pluck('name','id')->prepend('Select CHW', '')->toArray();

        $users['cm_users'] = User::whereHas('roles', function ($query) {
            $query->where('name', '=', CASEMANAGER);})
            ->where('status', 1)
            ->orderBy('name','asc')
            ->get()->pluck('name','id')->prepend('Select CM', '')->toArray();

        $users['md_users'] = User::whereHas('roles', function ($query) {
            $query->where('name', '=', MANAGERDIRECTOR);})
            ->where('status', 1)
            ->orderBy('name','asc')
            ->get()->pluck('name','id')->prepend('Select MD', '')->toArray();

        return $users;
    }

}
