<?php

use Illuminate\Support\Facades\Route;
use Biorev\Fivenine\Fivenine;

Auth::routes();

Route::any('/test-set',  [App\Http\Controllers\TestController::class, 'calculate']);
Route::any('/format-set',  [App\Http\Controllers\TestController::class, 'numberformating']);


//testing crone
Route::get('/testing-outreach-stage-update/{id}',  [App\Http\Controllers\TestingCroneController::class, 'stageUpdateInOutreach']);//outreach-data
Route::get('/testing-get-outreach-records/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getOutreachRecords']);
Route::get('/testing-update-all/{id}',  [App\Http\Controllers\TestingCroneController::class, 'updateAll']);//csynca.blade.php
//crone for account
Route::get('/testing-get-accounts/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getAccounts']);//csyncaccounts.blade.php
Route::get('/testing-get-tasks/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getOutreachTaks']);//csynTask.blade.php
Route::get('/testing-get-sequences/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getOutreachSequences']);//csynSequence.blade.php

//crone to get all outreach records
Route::get('/get-outreach-records/{id}',  [App\Http\Controllers\HomeController::class, 'getOutreachRecords']);
//crone to update prospects's state
Route::get('/outreach-stage-update',  [App\Http\Controllers\HomeController::class, 'stageUpdateInOutreach']);//outreach-data
//crone to update all propects
Route::get('/update-all',  [App\Http\Controllers\HomeController::class, 'updateAll']);//csynca.blade.php
//crone to update all account records in db
Route::get('/get-accounts',  [App\Http\Controllers\HomeController::class, 'getAccounts']);//csyncaccounts.blade.php
//crone to uptate all tasks in db
Route::get('/get-tasks',  [App\Http\Controllers\HomeController::class, 'getOutreachTaks']);//csynTask.blade.php
//crone to uptate all serquence in db
Route::get('/get-sequences',  [App\Http\Controllers\HomeController::class, 'getOutreachSequences']);//csynSequence.blade.php
//crone to uptate all sequence-states in db
Route::get('/get-sequence-states',  [App\Http\Controllers\HomeController::class, 'getOutreachSequenceStates']);//csynSequenceState.blade.php

Route::get('/update-custom',  [App\Http\Controllers\TestController::class, 'updateCustom']);

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () { return redirect('/dashboard'); })->name('home');    
    //Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('{path}', [App\Http\Controllers\HomeController::class, 'index'])->where('path', '([-a-z0-9_\/_.]+)?');
});


