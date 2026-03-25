<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tables', function () {return view('tables');})->name('tables');
Route::get('/datatables', function () {return view('data');})->name('datatables');
Route::get('/ui-elements', function () {return view('uiGeneral');})->name('ui-elements');
Route::get('/forms', function () {return view('forms');})->name('forms');
Route::get('/icons', function () {return view('icons');})->name('icons');
Route::get('/widgets', function () {return view('widgets');})->name('widgets');