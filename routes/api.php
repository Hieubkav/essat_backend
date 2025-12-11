<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API v1 routes
Route::prefix('v1')->group(base_path('routes/api/v1.php'));

// API v2 routes
// Route::prefix('v2')->group(base_path('routes/api/v2.php'));
