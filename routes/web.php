<?php

use App\Http\Controllers\ChartController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\OngController;
use App\Http\Controllers\PairsController;
use App\Http\Controllers\RandomizeController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* chart data */
Route::get('/chart', [ChartController::class, 'data'])->name('chart.data');

/* chart page */
Route::get('/pair', [ChartController::class, 'pair'])->name('pair');

/* main page */
Route::get('/', [HomeController::class, 'index'])->name('home');

/* real cron script */
Route::get('/check', [CronController::class, 'check'])->name('check');

/* manually checking / transfering */
Route::get('/transfer', [ManualController::class, 'transfer'])->name('transfer');
Route::get('/balance', [ManualController::class, 'balance'])->name('balance');

Route::get('/price', [ManualController::class, 'price'])->name('price');

/* logs */
Route::get('logs', [LogViewerController::class, 'index'])->name('logs');

/* pairs */
Route::get('pairs', [PairsController::class, 'index'])->name('saved.pairs');
Route::post('pairs', [PairsController::class, 'create'])->name('create.pair');
Route::post('pairs/delete', [PairsController::class, 'delete'])->name('delete.pair');

/* balrecord */
Route::get('/brecord', [ManualController::class, 'brecord'])->name('brecord');

/* something IEX related */
Route::get('/ong', [OngController::class, 'get']);

/* random pair */
Route::get('/randomize', [RandomizeController::class, 'randomPair'])->name('randomize');

/* trash a pair */
Route::post('/dudpair', [RandomizeController::class, 'trash'])->name('trash');

/* manually checking / transfering */
Route::get('/transfer', [ManualController::class, 'transfer'])->name('transfer-route');
Route::get('/position', [ManualController::class, 'position'])->name('position-route');
Route::get('/price', [ManualController::class, 'price'])->name('price');

/* check if market open */
Route::get('/open', [ManualController::class, 'open'])->name('open');
