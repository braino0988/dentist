<?php

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('employeelogin',[AuthController::class,'employeeLogin']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::get('/email/verify/{id}/{hash}',[AuthController::class,'verifyEmail'])->middleware(['signed'])->name('verification.verify');
Route::post('/email/resend',[AuthController::class, 'resendEmailVerification'])->middleware(['auth:sanctum'])->name('verification.resend');
Route::post('/createemployees',[AuthController::class,'create'])->middleware(['auth:sanctum','employee']);
Route::get('/customers',[AuthController::class,'indexCustomers'])->middleware(['auth:sanctum', 'employee']);
Route::get('/employees',[AuthController::class,'indexEmployees'])->middleware(['auth:sanctum', 'employee']);
//PRODUCT ROUTES
Route::get('/products',[App\Http\Controllers\Api\ProductController::class,'index']);
Route::post('/createproduct',[App\Http\Controllers\Api\ProductController::class,'store'])->middleware(['auth:sanctum','employee']);
Route::post('/updateproduct/{id}',[App\Http\Controllers\Api\ProductController::class, 'update'])->middleware(['auth:sanctum','employee']);
Route::delete('/deleteproduct/{id}',[App\Http\Controllers\Api\ProductController::class,'destroy'])->middleware(['auth:sanctum','employee']);
//CATEGORY ROUTES
Route::get('/categories',[App\Http\Controllers\Api\CategoryController::class,'index']);
Route::post('/createcategory',[App\Http\Controllers\Api\CategoryController::class,'store'])->middleware(['auth:sanctum','employee']);
Route::post('/updatecategory/{id}',[App\Http\Controllers\Api\CategoryController::class,'updateState'])->middleware(['auth:sanctum','employee']);
Route::delete('/deletecategory/{id}',[App\Http\Controllers\Api\CategoryController::class,'destroy'])->middleware(['auth:sanctum','employee']);