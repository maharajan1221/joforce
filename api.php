<?php

use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductTypesController;
use App\Http\Controllers\TasksController;

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomersController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('customers', CustomersController::class)->names('customers');
});

Route::get('customer',[CustomersController::class,'index']);
Route::post('customer',[CustomersController::class,'store']);


Route::get('tasks',[TasksController::class,'index']);
Route::post('tasks',[TasksController::class,'store']);


Route::get('tasks',[TasksController::class,'index']);
Route::post('tasks',[TasksController::class,'store']);

Route::get('products',[ProductsController::class,'index']);
Route::post('products',[ProductsController::class,'store']);

Route::get('product-type',[ProductTypesController::class,'index']);
Route::post('product-type',[ProductTypesController::class,'store']);

Route::get('product-categories',[ProductCategoriesController::class,'index']);
Route::post('product-categories',[ProductCategoriesController::class,'store']);


// Route::get('/tasks', [InvoicesController::class, 'getTasks']);
Route::get('invoices', [InvoicesController::class, 'fetchData']);
// Route::get('invoices/{value}', [InvoicesController::class, 'getDetails']);
Route::get('invoices/{type}/{value}', [InvoicesController::class, 'getDetails']);




