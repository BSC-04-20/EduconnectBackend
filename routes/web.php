<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/register-email", function(){
    return view("emails.register-mail");
});
