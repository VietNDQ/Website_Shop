<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/docs', function () {
    return view('api_docs');
});

Route::get('/run-storage-link', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return "Storage link created successfully!";
    } catch (\Exception $e) {
        return "Error creating storage link: " . $e->getMessage();
    }
});

