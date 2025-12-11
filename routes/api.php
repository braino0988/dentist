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
Route::post('/employees',[AuthController::class,'create'])->middleware(['auth:sanctum','employee']);
Route::get('/users',[AuthController::class,'index'])->middleware(['auth:sanctum', 'employee']);
Route::get('/products',[App\Http\Controllers\Api\ProductController::class,'index']);
Route::post('/categories',[App\Http\Controllers\Api\CategoryController::class,'store'])->middleware(['auth:sanctum','employee']);