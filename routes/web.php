<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});
Route::fallback(function () {
    abort(404);
    exit;
});
Auth::routes();
Route::resource('privileges', 'Admin\RoleController')->only([
    'index', 'edit', 'update'
]);
Route::resource('permissions', 'Admin\PermissionController');
//Route::resource('users', 'Admin\UserController');

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout')->name('logout');
Route::get('password-reset', 'PasswordController@showForm'); //I did not create this controller. it simply displays a view with a form to take the email
Route::post('password-reset', 'PasswordController@sendPasswordResetToken')->name('send-reset-otp');
Route::get('reset-password/{token}', 'PasswordController@showPasswordResetForm');
Route::post('reset-password', 'PasswordController@resetPassword')->name('reset-password-otp');
Route::post('verify-reset-otp', 'PasswordController@verifyOTP')->name('verify-reset-otp');
Route::post('resend-reset-otp', 'PasswordController@resendPasswordResetToken')->name('resend-reset-otp');


Route::post('keep-session-alive',function () {
    return "1";
})->name('keep-session-alive');



