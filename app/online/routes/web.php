<?php

use Illuminate\Support\Facades\Route;
use Biorev\Fivenine\Fivenine;
Route::get('/run-queue', function(){
    echo "queue is running";
    Artisan::call('queue:work --stop-when-empty', []);
    echo "<br>queue is stoped";
});

// testing
// Route::get('/testing', [App\Http\Controllers\TestController::class, 'testing']);
// Route::get('/update-contact/{id}', [App\Http\Controllers\JobsController::class, 'jobupdate']);

Route::get('/job-created/{record_id}/{field}/{value}', [App\Http\Controllers\JobsController::class, 'jobCreated']);

//get outreach token
// Route::get('/get-outreach-token', [App\Http\Controllers\OutreachTokenController::class, 'getToken']);
Route::get('/get-persona-from-outreach', [App\Http\Controllers\OutreachTokenController::class, 'getPersonaFromOutreach']);
Route::any('/outrech-update-all',[App\Http\Controllers\OutreachTokenController::class, 'outreachUpdateAll']);
Route::any('/get-prospect-info/{record_id}',[App\Http\Controllers\OutreachTokenController::class, 'getProspectInfo']);

Route::get('/get-five-nine-record/{number1}', [App\Http\Controllers\API\FiveNineController::class, 'getFive9Record']);
Route::get("/get-state/{id}", [App\Http\Controllers\API\ProspectsLocationController::class, 'states']);
Route::get("/fetchNxx/{id}", [App\Http\Controllers\API\ProspectsLocationController::class, 'fetchNxx']);
Route::get('/updateStateCity', [App\Http\Controllers\API\ProspectsLocationController::class, 'updateStateCity']);
Route::get('/timezoneUpdate', [App\Http\Controllers\API\ProspectsLocationController::class, 'timezoneUpdate']);
Route::get('/update-outreach-country-state-city-timezone/{id}', [App\Http\Controllers\API\ProspectsLocationController::class, 'updateOutreachContactsOnOutreachServer']);
Route::get('/update-timezone-on-outreach-server/{id}', [App\Http\Controllers\API\ProspectsLocationController::class, 'updateTimezoneOnOutreachServer']);

// used to export in dataset
Route::get('/update-all-prospect-email-count-back/{record_id}', [App\Http\Controllers\TestingController::class, 'updateAllProspectsEmailCountBack']);
Route::get('/update-all-prospect-email-count/{record_id}', [App\Http\Controllers\TestingController::class, 'updateAllProspectsEmailCount']);
Route::get('/update-all-prospects/{record_id}', [App\Http\Controllers\TestingController::class, 'updateAllProspects']);
Route::get('/update-all-prospects-back/{record_id}', [App\Http\Controllers\TestingController::class, 'updateAllProspectsBack']);
Route::get('/update-all-prospects-back2/{record_id}', [App\Http\Controllers\TestingController::class, 'updateAllProspectsBack2']);
Route::get('/get-list-info', [App\Http\Controllers\TestingController::class, 'getListInfo']);


Route::get('/dataFromFiveNineReport', [App\Http\Controllers\API\FiveNineController::class, 'dataFromFiveNineReport']);
Route::get('/udateEmailCounter/{i}', [App\Http\Controllers\LogReportsController::class, 'udateEmailCounter']);

Route::get("/set-five9-fields-data/{t}", [App\Http\Controllers\ServerCronController::class, 'setFive9FieldData']);

Route::any('/update-contact-name/{page}',[App\Http\Controllers\ServerCronController::class, 'updateContactName']);
Route::any('/change-date-format/{i}',[App\Http\Controllers\ServerCronController::class, 'changeDateFormat']);
Route::any('/change-date-format-switch/{i}',[App\Http\Controllers\ServerCronController::class, 'changeDateFormatSwitch']);
//daily crone

Route::any('/outrech-update-all-contacts-latest',[App\Http\Controllers\ServerCronLatestController::class, 'outreachUpdateAll']);

Route::any('/outrech-update-all-contacts',[App\Http\Controllers\ServerCronController::class, 'outreachUpdateAll']);
Route::any('/crone-prospect-counter',[App\Http\Controllers\ServerCronController::class, 'prospectCounter']);
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
Route::any('/my-test-make-temp-records-for-prospects/{page}',[App\Http\Controllers\MyTestCroneController::class, 'makeTempRecordsForProspects']);

Route::any('/update-contact-custom-field-from-outreach/{page}',[App\Http\Controllers\ServerCronController::class, 'updateContactCustomFieldFromOutrech']);//
Route::get('/update-from-five9-to-contacts/{day}',  [App\Http\Controllers\ServerCronController::class, 'updateFromFive9ToContacts']);

//server crone



Route::any('/update-call-count-in-contacts/{page}',[App\Http\Controllers\TestingCroneController::class, 'updateCallCountInContacts']);//update-call-count-in-contacts.blade.php
Route::get('/number-syncing-all/{page}',[App\Http\Controllers\TestingController::class, 'numbersyncing']);
//update outreach prospect's contacts
Route::any('/update-outreach-contacts-on-outreach-server/{record_id}',[App\Http\Controllers\TestingCroneController::class, 'updateOutreachContactsOnOutreachServer']);//update-outreach-contacts-on-outreach-server.blade.php
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
// Route::get('/testing-get-outreach-records/{id}',  [App\Http\Controllers\TestingCroneController::class, 'getOutreachRecords']);
// Route::get('/testing-get-outreach-records/{id}',  [App\Http\Controllers\ServerCronController::class, 'getOutreachRecordsTags']);
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


Route::get('/data-update', [App\Http\Controllers\DataUpdateController::class, 'index']);//csynMailings.blade.php


Route::middleware(['auth'])->group(function () {
    Route::get('/', function () { return redirect('/dashboard'); })->name('home');    
    Route::get('/contact-sync', [App\Http\Controllers\HomeController::class, 'contacts'])->name('sync');
    //Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('{path}', [App\Http\Controllers\HomeController::class, 'index'])->where('path', '([-a-z0-9_\/_.]+)?');
});

