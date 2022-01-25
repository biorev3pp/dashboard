<?php

use Illuminate\Support\Facades\Route;
use Biorev\Fivenine\Fivenine;
Route::get('/run-queue', function(){
    echo "queue is running";
    ini_set('max_execution_time', 600);
    Artisan::call('queue:work --stop-when-empty', []);
    echo "queue is stoped";
});
Route::get("/set-columns", [App\Http\Controllers\TestController::class, 'setColumn']);
Route::get("/get-state/{id}", [App\Http\Controllers\API\ProspectsLocationController::class, 'states']);
Route::get("/fetchNxx/{id}", [App\Http\Controllers\API\ProspectsLocationController::class, 'fetchNxx']);
Route::get('/updateStateCity', [App\Http\Controllers\API\ProspectsLocationController::class, 'updateStateCity']);
Route::get('/timezoneUpdate', [App\Http\Controllers\API\ProspectsLocationController::class, 'timezoneUpdate']);
Route::get('/update-outreach-country-state-city-timezone/{id}', [App\Http\Controllers\API\ProspectsLocationController::class, 'updateOutreachContactsOnOutreachServer']);
Route::get('/update-timezone-on-outreach-server/{id}', [App\Http\Controllers\API\ProspectsLocationController::class, 'updateTimezoneOnOutreachServer']);

Route::get('/dataFromFiveNineReport', [App\Http\Controllers\API\FiveNineController::class, 'dataFromFiveNineReport']);
Route::get('/udateEmailCounter/{i}', [App\Http\Controllers\LogReportsController::class, 'udateEmailCounter']);

Route::get("/set-five9-fields-data/{t}", [App\Http\Controllers\ServerCronController::class, 'setFive9FieldData']);

Route::any('/update-contact-name/{page}',[App\Http\Controllers\ServerCronController::class, 'updateContactName']);
Route::any('/change-date-format/{i}',[App\Http\Controllers\ServerCronController::class, 'changeDateFormat']);
Route::any('/change-date-format-switch/{i}',[App\Http\Controllers\ServerCronController::class, 'changeDateFormatSwitch']);
//daily crone
Route::any('/outrech-update-all-contacts',[App\Http\Controllers\ServerCronController::class, 'outreachUpdateAll']);
Route::get('/update-from-five9-to-contacts-crone',  [App\Http\Controllers\ServerCronController::class, 'updateFromFive9ToContactsCrone']);
Route::any('/get-outreach-mailing',[App\Http\Controllers\ServerCronController::class, 'getOutreachMailing']);
Route::any('/crone-email-counter',[App\Http\Controllers\ServerCronController::class, 'emailCounter']);
Route::any('/getfive9-last-12hrs-call-log',[App\Http\Controllers\ServerCronController::class, 'getFive9Last12hrsCallLog']);
Route::any('/crone-call-counter',[App\Http\Controllers\ServerCronController::class, 'callCounter']);
//Logs crone
Route::any('/sink-log-email-single',[App\Http\Controllers\LogReportsController::class, 'sinkLogEmailSingle']);
Route::any('/sink-log-email-sequence',[App\Http\Controllers\LogReportsController::class, 'sinkLogEmailSequence']);
Route::any('/sink-log-email-all',[App\Http\Controllers\LogReportsController::class, 'sinkLogEmailAll']);
Route::any('/sink-log-calls',[App\Http\Controllers\LogReportsController::class, 'sinkLogCalls']);
Route::get('/crone-agent-occupancy', [App\Http\Controllers\ServerCronController::class, 'croneAgentOccupancy']);
Route::get('/crone-set-wait-time', [App\Http\Controllers\ServerCronController::class, 'setWaitTime']);

//outreach email : log
Route::any('/report-single-email-data',[App\Http\Controllers\ServerCronController::class, 'reportSingleEmailData']);
Route::any('/report-sequence-email-data',[App\Http\Controllers\ServerCronController::class, 'reportSequenceEmailData']);

//Route::any('/outreach-email-update',[App\Http\Controllers\ServerCronController::class, 'getLast12HrsMailing']);

Auth::routes();

Route::any('/update-contact-custom-field-from-outreach/{page}',[App\Http\Controllers\ServerCronController::class, 'updateContactCustomFieldFromOutrech']);//
Route::get('/update-from-five9-to-contacts/{day}',  [App\Http\Controllers\ServerCronController::class, 'updateFromFive9ToContacts']);

//server crone

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


Route::get('/data-update', [App\Http\Controllers\DataUpdateController::class, 'index']);//csynMailings.blade.php


Route::middleware(['auth'])->group(function () {
    Route::get('/', function () { return redirect('/dashboard'); })->name('home');    
    Route::get('/contact-sync', [App\Http\Controllers\HomeController::class, 'contacts'])->name('sync');
    //Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('{path}', [App\Http\Controllers\HomeController::class, 'index'])->where('path', '([-a-z0-9_\/_.]+)?');
});

