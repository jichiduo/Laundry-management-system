<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\DB;

//multi language
//set locale
Route::get('/lang/{locale}', function ($locale) {
    //save locale to user table
    if (Auth()->user()) {
        DB::table('users')->where('id', Auth()->user()->id)
            ->update(['language' => $locale]);
    }
    session()->put('locale', $locale);
    return redirect()->back();
})->name('language');

// Users will be redirected to this route if not logged in
Volt::route('/login', 'login')->name('login');

// Define the logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
});

// Protected routes here
Route::middleware('auth')->group(function () {
    Volt::route('/', 'dashboard');
    Volt::route('/profile', 'settings.profile');
    Volt::route('/customer', 'customer');
    Volt::route('/workorder/new', 'workorder.new');
    Volt::route('/workorder/update/{id}', 'workorder.update')->name('workorderupdate');
    Volt::route('/workorder/list', 'workorder.list');
    //settings
    Volt::route('/user', 'settings.user');
    Volt::route('/app-group', 'settings.app_group');
    Volt::route('/division', 'settings.division');
    Volt::route('/product', 'settings.product');
    Volt::route('/type', 'settings.type');
    Volt::route('/currency', 'settings.currency');
    //for future use
    //Volt::route('/account-code', 'settings.acc_code');
    //Volt::route('/exchange-rate', 'settings.exchange_rate');
});
