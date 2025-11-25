<?php

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::get('/email/verify/{id}/{hash}',[AuthController::class,'verifyEmail'])->middleware(['signed'])->name('verification.verify');
Route::post('/email/resend',[AuthController::class, 'resendEmailVerification'])->middleware(['auth:sanctum'])->name('verification.resend');
