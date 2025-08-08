<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () { return 'Login page placeholder'; })->name('login');
Route::get('/register', function () { return 'Register page placeholder'; })->name('register');
