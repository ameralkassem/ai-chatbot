<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// الرابط سيكون: http://127.0.0.1:8000/api/chat
Route::post('/chat', [ChatController::class, 'sendMessage']);
