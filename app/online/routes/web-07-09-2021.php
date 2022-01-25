<?php

use Illuminate\Support\Facades\Route;
use Biorev\Fivenine\Fivenine;

Auth::routes();

//server crone
Route::any('/outrech-update-all',[App\Http\Controllers\ServerCronController::class, 'outreachUpdateAll']);
Route::any('/outreach-email-update',[App\Http\Controllers\ServerCronController::class, 'getLast12HrsMailing']);
Route::any('/fivenine-call-log',[App\Http\Controllers\ServerCronController::class, 'getFive9Last12hrsCallLog']);
Route::any('/update-call-count-in-contacts/{page}',[App\Http\Controllers\TestingCroneController::class, 'updateCallCountInContacts']);//update-call-count-in-contacts.blade.php
Route::get('/number-syncing-all/{page}',[App\Http\Controllers\TestingController::class, 'numbersyncing']);

Route::get('/tsync/{$i}/{$e}',[App\Http\Controllers\TestController::class, 'numbersyncing']);

Route::get('/prospect-contact-swap/{page}',[App\Http\Controllers\TestingController::class, 'prospectContactSwap']);
Route::get('/prospect-contact-hq-swap/{page}',[App\Http\Controllers\TestingController::class, 'prospectContactHqSwap']);
Route::get('/prospect-contact-other-swap/{page}',[App\Http\Controllers\TestingController::class, 'prospectContactOtherSwap']);


Route::get('/showCotacts',[App\Http\Controllers\TestingCroneController::class, 'showCotacts']);

Route::get('/email-count/{id}',[App\Http\Controllers\TestController::class, 'emailCounter']);

//contact number formatin  
Route::get('/contact-number-formating', [App\Http\Controllers\TestingCroneController::class, 'numberformating']);
//fivenive call log update number-type
Route::get('/fivenine-number-type-update', [App\Http\Controllers\TestingCroneController::class, 'numbersyncing']);
Auth::routes();
Route::get('/test', [App\Http\Controllers\DatasetsTestController::class, 'rundatasets']);
Route::any('/test-set',  [App\Http\Controllers\TestController::class, 'calculate']);
//backup five9 : call log
Route::get('/backup-get-five9-cal-log/{$i}', [App\Http\Controllers\BackupFive9Controller::class, 'getfive9CallLog']);//BackupFive9CallLog.blade.php
//testing crone
Route::get('/testing-outreach-stage-update/{id}',  [App\Http\Controllers\TestingCroneController::class, 'stageUpdateInOutreach']);//outreach-data
Route::get('/testing-get-outreach-records/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getOutreachRecords']);
Route::get('/testing-update-all/{id}',  [App\Http\Controllers\TestingCroneController::class, 'updateAll']);//csynca.blade.php
//get outreach custom data
Route::get('/get-outreach-custom-data/{i}', [App\Http\Controllers\TestingCroneController::class, 'getOutreachCustomData']);//outreachCustomData.blade.php
Route::get('/get-custom-fields', [App\Http\Controllers\TestingCroneController::class, 'getCustomFields']);
//crone for account
Route::get('/testing-get-accounts/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getAccounts']);//csyncaccounts.blade.php
Route::get('/testing-get-tasks/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getOutreachTaks']);//csynTask.blade.php
Route::get('/testing-get-sequences/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getOutreachSequences']);//csynSequence.blade.php
Route::get('/testing-get-sequence-states/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getOutreachSequenceStates']);//csynSequenceState.blade.php
//prospect mailings data
Route::get('/testing-get-outreach-mailings/{id}', [App\Http\Controllers\TestingCroneController::class, 'getOutreachMailings']);//csynMailings.blade.php

//five9 call log
Route::get('/testing-get-five9-call-log/{id}', [App\Http\Controllers\TestingCroneController::class, 'getFive9CallLog']);
//last 12 hrs call log
Route::get('/testing-get-five9-last-12hrs-call-log', [App\Http\Controllers\TestingCroneController::class, 'getFive9Last12hrsCallLog']);

Route::get('/testing-get-last12-hrs-mailing', [App\Http\Controllers\TestingCroneController::class, 'getLast12HrsMailing']);
//crone to get all outreach records
Route::get('/get-outreach-records',  [App\Http\Controllers\HomeController::class, 'getOutreachRecords']);
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
//prospect mailings data
Route::get('/get-outreach-mailings', [App\Http\Controllers\HomeController::class, 'getOutreachMailings']);//csynMailings.blade.php
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () { return redirect('/dashboard'); })->name('home');    
    Route::get('/contact-sync', [App\Http\Controllers\HomeController::class, 'contacts'])->name('sync');
    //Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('{path}', [App\Http\Controllers\HomeController::class, 'index'])->where('path', '([-a-z0-9_\/_.]+)?');
});

