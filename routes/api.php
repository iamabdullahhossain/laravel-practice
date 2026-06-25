<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController; // ইমপোর্ট করা হলো

// --- পাবলিক রাউটস (অথেনটিকেশন ছাড়াই অ্যাক্সেস করা যাবে) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- প্রটেক্টেড রাউটস (শুধুমাত্র ভ্যালিড Bearer Token থাকলে অ্যাক্সেস করা যাবে) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // কারেন্ট অথেনটিকেটেড ইউজারের প্রোফাইল ডাটা দেখাবে
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // লগআউট রাউট
    Route::post('/logout', [AuthController::class, 'logout']);

    // ক্যাটাগরি এবং টাস্কস রিসোর্স রাউটস
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tasks', TaskController::class);
});
