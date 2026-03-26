<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {return view('login');})->name('login');
Route::get('/register', function () {return view('plantilla.register');})->name('register');
Route::get('/forgot-password', function () {return view('plantilla.forgot-password');})->name('forgot-password');
Route::get('/reset-password', function () {return view('plantilla.reset-password');})->name('reset-password');

Route::get('/tables', function () {return view('plantilla.tables');})->name('tables');
Route::get('/datatables', function () {return view('plantilla.datatables');})->name('datatables');
Route::get('/ui-elements', function () {return view('plantilla.ui-elements');})->name('ui-elements');
Route::get('/forms', function () {return view('plantilla.forms');})->name('forms');
Route::get('/icons', function () {return view('plantilla.icons');})->name('icons');
Route::get('/widgets', function () {return view('plantilla.widgets');})->name('widgets');