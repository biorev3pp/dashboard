<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/run-queue', function(){
    echo "queue is running";
    ini_set('max_execution_time', 600);
    Artisan::call('queue:work --stop-when-empty', []);
    echo "queue is stopped";
});
Route::post("/get-prospects-location", [App\Http\Controllers\API\ProspectsLocationController::class, 'index']);
//agent occupancy

Route::post('/get-agent-occupancy-login_time', [App\Http\Controllers\API\AgentOccupancyController::class, 'login_time']);
Route::post('/get-agent-occupancy-available-time', [App\Http\Controllers\API\AgentOccupancyController::class, 'available_time']);
Route::post("/get-agent-based-talk-time-status",    [App\Http\Controllers\API\AgentOccupancyController::class, 'getAgentBasedTalkTime']);
Route::post('/get-agent-proficiency', [App\Http\Controllers\API\AgentOccupancyController::class, 'on_call_time']);
Route::post('/get-agent-based-after-call-work', [App\Http\Controllers\API\AgentOccupancyController::class, 'after_call_work']);
Route::post('/get-agent-productivity-time', [App\Http\Controllers\API\AgentOccupancyController::class, 'productivity_time']);
Route::post('/get-total-call-and-disposition-total-call', [App\Http\Controllers\API\AgentOccupancyController::class, 'totalCallAndDisposition']);

Route::get('/randon-string', [App\Http\Controllers\API\DatasetExportController::class, 'randomString']);
//call dashboard
Route::post("/get-agent-call-status", [App\Http\Controllers\API\DashboardCallController::class, 'getAgentCallStatus']);
Route::post("/get-agent-disposition-status", [App\Http\Controllers\API\DashboardCallController::class, 'getAgentDispositionStatus']);
Route::post("/get-agent-occupancy-data", [App\Http\Controllers\API\DashboardCallController::class, 'getAgentOccupancyData']);
Route::post("/get-agent-based-talk-time",    [App\Http\Controllers\API\DashboardCallController::class, 'getAgentBasedTalkTime']);
Route::post("/get-agent-based-ACW",          [App\Http\Controllers\API\DashboardCallController::class, 'getAgentBasedACW']);
Route::post("/get-agent-based-waiting-time", [App\Http\Controllers\API\DashboardCallController::class, 'getAgentBasedWaitingTime']);
Route::post("/get-agent-info", [App\Http\Controllers\API\DashboardCallController::class, 'getAgentInfo']);
Route::post("/get-dashboard-time", [App\Http\Controllers\API\DashboardCallController::class, 'getDashboardTime']);

Route::get('/get-record-disposition/{record_id}', [App\Http\Controllers\API\DatasetsController::class, 'getRecordDispositions']);

Route::get('/get-graph-filters', [App\Http\Controllers\API\SettingsController::class, 'getGraphFilters']);
Route::post('/update-graph-filters', [App\Http\Controllers\API\SettingsController::class, 'updateGraphFilterOrder']);

Route::post('/get-dashboard-chart-with-filter',[App\Http\Controllers\API\ChartsController::class, 'index']);
Route::any('/get-dashboard-chart1',[App\Http\Controllers\API\ChartsController::class, 'chart1']);
Route::any('/get-dataset-graph-filter',[App\Http\Controllers\API\DatasetsController::class, 'chartFilter']);
Route::any('/get-graph-search-criteria',[App\Http\Controllers\API\DatasetsController::class, 'getGraphSearchCriteria']);
Route::get('/get-stage-oid',[App\Http\Controllers\API\DatasetsController::class, 'getStageOid']);


Route::get('/get-report-records', [App\Http\Controllers\API\ReportController::class, 'index']);
Route::post('/get-log-details', [App\Http\Controllers\API\ReportController::class, 'logRecords']);

Route::get('/get-all-purchase-authorization', [App\Http\Controllers\API\FilterController::class, 'getAllPurchaseAuthorization']);
Route::get('/get-all-departments', [App\Http\Controllers\API\FilterController::class, 'getAllDepartments']);
Route::get('/get-all-industry', [App\Http\Controllers\API\FilterController::class, 'getAllIndustry']);
Route::get('/get-all-primary-industry', [App\Http\Controllers\API\FilterController::class, 'getAllPrimaryIndustry']);
Route::get('/get-all-company-revenue', [App\Http\Controllers\API\FilterController::class, 'getAllCompanyRevenue']);
Route::get('/get-all-company-rev-range', [App\Http\Controllers\API\FilterController::class, 'getAllCompanyRevRange']);
Route::get('/get-all-time-zone', [App\Http\Controllers\API\FilterController::class, 'getAllTimeZone']);
Route::get('/get-all-cities', [App\Http\Controllers\API\FilterController::class, 'getAllCities']);
Route::get('/get-all-states', [App\Http\Controllers\API\FilterController::class, 'getAllStates']);
Route::get('/get-all-zip-code', [App\Http\Controllers\API\FilterController::class, 'getAllZipCode']);
Route::get('/get-all-outreach-tags', [App\Http\Controllers\API\FilterController::class, 'getAllOutreachTags']);
Route::get('/get-all-source', [App\Http\Controllers\API\FilterController::class, 'getAllSources']);
Route::get('/get-all-country', [App\Http\Controllers\API\FilterController::class, 'getAllCountries']);

Route::apiResource('/dataset-values', App\Http\Controllers\API\DatasetsController::class);
Route::apiResource('/tasks', App\Http\Controllers\API\TasksController::class);

Route::apiResource('/dashboard-designs', App\Http\Controllers\API\DashboardDesignsController::class);

Route::get('/data-check', [App\Http\Controllers\API\DatasetsController::class, 'datacheck']);
Route::get('/get-record-disposition/{record_id}', [App\Http\Controllers\API\DatasetsController::class, 'getRecordDispositions']);
Route::post('/dataset-values-data', [App\Http\Controllers\API\DatasetsController::class, 'getAllData']);
Route::post('/dataset-values-data-all', [App\Http\Controllers\API\DatasetsController::class, 'getFullData']);
Route::post('/dataset-values-data-call', [App\Http\Controllers\API\DashboardCallController::class, 'getAllDataCall']);
Route::post('/dataset-values-data-call-all', [App\Http\Controllers\API\DashboardCallController::class, 'getFullDataCall']);

Route::post('/reset-dataset', [App\Http\Controllers\API\DatasetsController::class, 'resetDataset']);
Route::get('/get-dataset', [App\Http\Controllers\API\DatasetsController::class, 'getDataset']);
Route::post('/get-record-container-info', [App\Http\Controllers\API\DatasetsController::class, 'getRecordContainerInfo']);
Route::post("/export-graph-data-to-fivenine", [App\Http\Controllers\API\DatasetExportController::class, 'export']);
// Common API
Route::any('/get-job-history', [App\Http\Controllers\API\JobHistoryController::class, 'index']);

Route::get('/get-configs', [App\Http\Controllers\API\SettingsController::class, 'GetConfigs']);
Route::get('/get-f9-list', [App\Http\Controllers\API\FiveNineController::class, 'getContactList']);
Route::get('/get-f9-list-from-five9', [App\Http\Controllers\API\FiveNineController::class, 'getContactListFromF9']);
Route::get('/get-five9-full-list', [App\Http\Controllers\API\FiveNineController::class, 'getContactFullList']);
Route::get('/get-f9-campaigns', [App\Http\Controllers\API\FiveNineController::class, 'getContactCampaigns']);
Route::get('/get-f9-dispositions', [App\Http\Controllers\API\FiveNineController::class, 'getContactDispositions']);
Route::get('/get-f9-skills', [App\Http\Controllers\API\FiveNineController::class, 'getContactSkills']);
Route::get('/get-f9-list-details/{lid}', [App\Http\Controllers\API\FiveNineController::class, 'getFivenineListDetails']);
Route::get('/get-list-based-stages/{lid}', [App\Http\Controllers\API\FiveNineController::class, 'getListBasedStages']);
Route::get('/get-record-id-based-lists/{record_id}', [App\Http\Controllers\API\FiveNineController::class, 'getRecordIdBasedLists']);
Route::post('/get-list-based-all-prospects', [App\Http\Controllers\API\FiveNineController::class, 'getListbasedProspects']);
Route::post('/get-list-matched-data', [App\Http\Controllers\API\FiveNineController::class, 'getListMatchedData']);
Route::post('/get-number-based-on-disposition', [App\Http\Controllers\API\FiveNineController::class, 'getNumberBasedOnDispositionAndNoneAgent']);
Route::get('/get-list-history', [App\Http\Controllers\API\FiveNineController::class, 'getListHistory']);
Route::get('/get-list-import-result', [App\Http\Controllers\API\FiveNineController::class, 'getListImportResult']);
Route::get('/remove-history-from-list', [App\Http\Controllers\API\FiveNineController::class, 'removeHistoryFromList']);

Route::get('/get-f9-countries', [App\Http\Controllers\API\FiveNineController::class, 'getCountryList']);
Route::get('/get-mapping-range', [App\Http\Controllers\API\SettingsController::class, 'MappingRange']);
Route::get('/get-all-stages', [App\Http\Controllers\API\SettingsController::class, 'AllStages']);
Route::get('/get-stages-data', [App\Http\Controllers\API\SettingsController::class, 'AllStagesData']);
Route::get('/get-all-datasets', [App\Http\Controllers\API\SettingsController::class, 'AllDatasets']);
Route::get('/get-all-dataset-groups', [App\Http\Controllers\API\SettingsController::class, 'AllFilterDatasets']);
Route::get('/get-all-agent-lists', [App\Http\Controllers\API\OutreachController::class, 'AllAgents']);
Route::get('/get-all-last-dispo', [App\Http\Controllers\API\OutreachController::class, 'AllLastDispo']);
Route::get('/get-agent-info',  [App\Http\Controllers\API\DashboardCallController::class, 'getAgentInformation']);
Route::get('/get-search-criteria-info',  [App\Http\Controllers\API\DashboardCallController::class, 'getSearchCriteriaInfo']);



Route::get('/hit-crone-manually', [App\Http\Controllers\API\JobHistoryController::class, 'updateall']);
Route::get('/get-agents-information', [App\Http\Controllers\API\FiveNineController::class, 'agentReport']);
// Template APIs
Route::get('/get-type-template/{id}', [App\Http\Controllers\API\TemplateController::class, 'getTypeTemplate']);
Route::post('/add-template', [App\Http\Controllers\API\TemplateController::class, 'addTemplate']);


// Milestone 1 *****  CSV Export - Five9 Import
Route::post('/uploadContactsExport', [App\Http\Controllers\API\CsvExportController::class, 'uploadContactsExport']);

Route::post('/get-outreach-updated-data', [App\Http\Controllers\API\JobHistoryController::class, 'updateddata']);


//outreach dashboard
Route::post('/get-outreach-all-prospects-dashboard', [App\Http\Controllers\API\OutreachController::class, 'outreachAllprospects']);
Route::get('/get-outreach-prospect-synopsis-all-data', [App\Http\Controllers\API\SettingsController::class, 'getOutreachProspectSynopsisAllData']);
Route::get('/get-outreach-stages', [App\Http\Controllers\API\OutreachController::class, 'getOutreachStages']);
Route::get('/get-outreach-stages-record', [App\Http\Controllers\API\OutreachController::class, 'getOutreachStagesRecords']);
Route::get('/get-outreach-prospect-activities/{id}', [App\Http\Controllers\API\OutreachController::class, 'outreachprospectDetailsActivities']);
Route::get('/get-outreach-prospect-details-calls/{id}', [App\Http\Controllers\API\OutreachController::class, 'outreachprospectDetailsCalls']);
Route::get('/get-outreach-prospect-details-emails/{id}', [App\Http\Controllers\API\OutreachController::class, 'outreachprospectDetailsEmails']);
Route::get('/get-outreach-prospect-details-events/{id}', [App\Http\Controllers\API\OutreachController::class, 'outreachprospectDetailsEvents']);


Route::get('/get-outreach-all-prospects-to-export', [App\Http\Controllers\API\OutreachController::class, 'outreachAllprospectToExport']);

Route::get('/modifiystage', [App\Http\Controllers\API\OutreachController::class, 'modifidata']);

Route::get('/get-all-filter', [App\Http\Controllers\API\OutreachController::class, 'getAllFilter']);
Route::get('/get-all-filter-for-accounts', [App\Http\Controllers\API\OutreachController::class, 'getAllFilterForAccounts']);
Route::get('/get-all-filter-dataset', [App\Http\Controllers\API\OutreachController::class, 'getAllFilterDataset']);
Route::get('/get-all-outreach-stages', [App\Http\Controllers\API\OutreachController::class, 'AllStages']);

Route::post('/get-outreach-account-lists', [App\Http\Controllers\API\OutreachController::class, 'getOutreachAccountLists']);
Route::get('/get-account-info/{id}', [App\Http\Controllers\API\OutreachController::class, 'getOutreachAccountInfo']);
//outreach prospects details
Route::get('/get-outreach-prospect-details/{id}', [App\Http\Controllers\API\OutreachController::class, 'outreachprospectDetails']);
Route::get('/get-call-dispositions', [App\Http\Controllers\API\OutreachController::class, 'getCallDispositions']);
Route::get('/get-all-prospects-email', [App\Http\Controllers\API\OutreachController::class, 'getAllProspectsEmail']);
Route::get('/get-all-prospects-calls', [App\Http\Controllers\API\OutreachController::class, 'getAllProspectsCall']);
Route::get('/get-call-purpose', [App\Http\Controllers\API\OutreachController::class, 'getCallPurpose']);
Route::get('/get-all-users', [App\Http\Controllers\API\OutreachController::class, 'getAllUsers']);
//view
Route::get('/get-all-views', [App\Http\Controllers\API\OutreachController::class, 'allViews']);
Route::get('/get-all-views-for-accounts', [App\Http\Controllers\API\OutreachController::class, 'allViewsAccounts']);
Route::post('/save-view', [App\Http\Controllers\API\OutreachController::class, 'saveView']);
Route::post('/save-view-accounts', [App\Http\Controllers\API\OutreachController::class, 'saveViewAccounts']);
Route::get('/get-history-export', [App\Http\Controllers\API\ImportController::class, 'getHistoryExport']);
Route::get('/get-single-history/{id}', [App\Http\Controllers\API\ImportController::class, 'getSingleHistoryExport']);

Route::get('/view-export-reports/{id}', [App\Http\Controllers\API\ImportController::class, 'getReport']);

Route::get('/get-reports-detail/{id}', [App\Http\Controllers\API\ImportController::class, 'getReportsDetail']);

Route::post('/get-account-prospects', [App\Http\Controllers\API\OutreachController::class, 'getAccountProspects']);
Route::get('/get-all-stages-account/{id}', [App\Http\Controllers\API\OutreachController::class, 'AllStagesAccount']);
//job-hostory
Route::get('/get-contacts', [App\Http\Controllers\API\JobHistoryController::class, 'getContatcs']);

// five9
Route::get('/get-five-nine-report', [App\Http\Controllers\API\FiveNineController::class, 'report']);
Route::get('/get-five-nine-report-response/{id}', [App\Http\Controllers\API\FiveNineController::class, 'getReportResponse']);
Route::get('/get-five-nine-report-result/{id}', [App\Http\Controllers\API\FiveNineController::class, 'getReportResult']);
Route::post('/get-call-reports', [App\Http\Controllers\API\FiveNineController::class, 'callReport']);
Route::post('/get-call-reports-01', [App\Http\Controllers\API\FiveNineController::class, 'callReportOne']);
Route::post('/get-five-nine-call-log-report-result', [App\Http\Controllers\API\FiveNineController::class, 'callReportResult']);
Route::post('/get-five-nine-call-log-report-result-01', [App\Http\Controllers\API\FiveNineController::class, 'callReportResultOne']);
Route::post('/get-export-call-contacts', [App\Http\Controllers\API\FiveNineController::class, 'getExportCallContacts']);
Route::get('/get-f9-list-details/{lid}', [App\Http\Controllers\API\FiveNineController::class, 'getFivenineListDetails']);

Route::get('/delete-form-list', [App\Http\Controllers\API\FiveNineController::class, 'deleteFromList']);
Route::post('/delete-selected-form-list', [App\Http\Controllers\API\FiveNineController::class, 'deleteSelectedFromList']);
Route::post('/transfer-contacts', [App\Http\Controllers\API\FiveNineController::class, 'transferContacts']);
Route::post('/update-disposition', [App\Http\Controllers\API\FiveNineController::class, 'updateDisposition']);
Route::get('/get-campaign-list', [App\Http\Controllers\API\FiveNineController::class, 'getCampaignLists']);
Route::get('/start-campaign', [App\Http\Controllers\API\FiveNineController::class, 'startCampaign']);
Route::get('/stop-campaign', [App\Http\Controllers\API\FiveNineController::class, 'stopCampaign']);
Route::post("/add-list-to-campaign", [App\Http\Controllers\API\FiveNineController::class, 'addListToCampaign']);

Route::get('/delete-list-from-campaign', [App\Http\Controllers\API\FiveNineController::class, 'deleteListFromCampaign']);
Route::get('/delete-all-from-list', [App\Http\Controllers\API\FiveNineController::class, 'deleteAllFromList']);
Route::get('/delete-list', [App\Http\Controllers\API\FiveNineController::class, 'deleteList']);
Route::get('/create-new-list/{listName}', [App\Http\Controllers\API\FiveNineController::class, 'createNewList']);
Route::get('/update-list', [App\Http\Controllers\API\FiveNineController::class, 'updateList']);

//testing
Route::get('/get-five-nine-all-list-report', [App\Http\Controllers\API\FiveNineController::class, 'getAllList']);
Route::get('/get-five-nine-all-list-report-results/{id}', [App\Http\Controllers\API\FiveNineController::class, 'getAllListResults']);
Route::post('/outreach-records', [App\Http\Controllers\API\OutreachController::class, 'getOutreachRecords']);


