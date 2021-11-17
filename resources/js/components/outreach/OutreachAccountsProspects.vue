

<template>
    <div>
        <div class="row">
            <div class="col-md-9">
                <div v-if="step == 0">
                    <div class="table-responsive">
                        <div class="synops">          
                            <div class="inner-synop cursor-pointer" :class="[(form.stage == 'all')?'active':'']" @click="filterStage('all')">
                                <h4 class="number">{{ totalNumberOfRecords | freeNumber }} </h4><p>Total</p>
                            </div>
                            <div class="inner-synop cursor-pointer" v-for="(stageDetail, i) in stageDetails"  :key="'row-'+i" :class="[(stageDetail.oid == form.stage)?'active':'']" @click="filterStage(stageDetail.oid)">
                                <h4 class="number">{{ stageDetail.count | freeNumber }} </h4><p>{{ stageDetail.stage }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="filterbox">
                        <div class="row m-0">
                            <div class="col-md-4 col-12 pl-0">
                                <button type="button" class="btn btn-sm btn-outline-dark" @click="showView=true" v-if="view">{{view.view_name}}</button>
                                <button type="button" class="btn btn-sm btn-outline-dark" @click="showView=true" v-else>Default View</button>
                                
                                <a href="javascript:void(0)" class="link-primary" @click="showViewPopup"><i class="bi bi-plus"></i> Save View</a>
                                <v-select v-if="showView" label="view_name" :options="views" @input="selectView" v-model="view"></v-select>
                            </div>
                            <div class="col-md-8 col-12 pr-0 text-right form-inline d-block">
                                <span class="mr-2">
                                    <label class="form-control  pr-0  border-none"> Sort By : </label>
                                    <select class="form-control" v-model="form.sortType" @change="getOutreachData(1)">
                                        <option value="outreach_touched_at">Last Contacted</option>
                                        <option value="last_outreach_engage">Last Engaged</option>
                                        <option value="last_update_at">Last Updated</option>
                                        <option value="fivenine_created_at">Last Created</option>
                                        <option value="engage_score">Engagement</option>
                                        <option value="first_name">First Name</option>
                                        <option value="last_name">Last Name</option>
                                        <option value="company">Comapany Name</option>
                                        <option value="title">Title</option>
                                    </select>
                                </span>
                                <span class="mr-2">
                                    <i class="bi bi-arrow-down active pointer-hand" v-if="form.sortBy == 'desc'" @click="updateSorting('asc')"></i>
                                    <i class="bi bi-arrow-up active pointer-hand" v-else  @click="updateSorting('desc')"></i>
                                </span>
                                <span class="ml-4">
                                    <button class="btn btn-sm btn-default" @click="refreshAll"> 
                                        <i class="bi bi-bootstrap-reboot"></i> Refresh
                                    </button>
                                    
                                </span>
                            </div>
                        </div>
                        <div class="row mx-0 my-2">
                            <div class="col-md-3 pl-0">
                                <input type="text" class="form-control" v-model="form.textSearch" placeholder="Search by name, email or company" @input="getFilterData">
                            </div>
                            <div class="col-md-6 col-12 p-0">
                                <span v-for="(filter,index) in form.filterConditionsArray" :key="'filter-'+filter.conditionText+'-'+filter.formula+'-'+filter.textCondition" class="filter-btns" v-show="filter_expand">
                                    <span class="text-primary mx-1 pointer-hand" @click="showFilterDetails(filter, index)"> {{filter.textConditionLabel }}</span>
                                    <i class="bi bi-x pr-1  pointer-hand" @click="removeFilter(index)"></i>
                                </span>
                                <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" @click="showFilter"><i class="bi bi-plus"></i> Add filter</a>
                                <div class="stage-select-box" v-show="filter">
                                    <div class="form-group" v-show="filter">
                                        <v-select label="filter" :options="filterItems" @input="showFilterOption" v-model="form.filter"></v-select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 pr-1">
                                            <select  class="form-control" v-model="form.filterOption" v-if="(filterInput || filterDropdown) && (filter != null)">
                                                <option v-for="filterOption in filterOptions" :key="'select-'+filterOption" :value="filterOption">{{ filterOption }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-8 pl-1">
                                            <div v-show="filterInput">
                                                <input type="text" class="form-control" v-model="form.filterText" placeholder="">
                                            </div>
                                            <div v-show="filterDropdown">
                                                <div class="selectedOptions">
                                                    <span class="badge bg-primary text-white p-2 m-1 pointer-hand" v-for="(option, index) in selectedOptions" :key="'option-'+index" @click="removeSelectedOption(index)">{{ option }} </span> 
                                                </div>
                                                <select class="form-control" v-model="form.dropdown" @change="getSelectedOptions">
                                                    <option v-for="select in selects" :key="'select-'+select.oid" :value="select.oid">{{ select.stage }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-show="filterDateRange">
                                        <date-range-picker ref="picker" :locale-data="{ firstDay: 1, format: 'dd-mm-yyyy HH:mm:ss' }" :timePicker24Hour="false" :showWeekNumbers="true" :showDropdowns="true"  :dateFormat="dateFormat" :autoApply="false" v-model="form.dateRange" >
                                            <template v-slot:input="picker">
                                                <span v-if="form.dateRange.startDate">
                                                    {{ picker.startDate | setusdate }} - {{ picker.endDate | setusdate }}
                                                </span>
                                                <span v-else>
                                                    Select Date Range
                                                </span>
                                            </template>
                                        </date-range-picker>
                                    </div>
                                    <br>
                                    <button v-show="filterBtn" v-if="filterInput || filterDropdown || filterDateRange" class="btn btn-primary btn-sm" @click="createFilter">Done</button>
                                    <button v-show="!filterBtn" v-if="filterInput || filterDropdown || filterDateRange" class="btn btn-primary btn-sm" @click="updateFilter">Update</button>
                                </div>
                                <span class="text-secondary cursor-pointer ml-1" v-if="filter_expand" @click="filter_expand = false">Hide Filters</span>
                                <span class="text-secondary cursor-pointer ml-1" v-else @click="filter_expand = true">{{ form.filterConditionsArray.length }} Hidden Filters</span>
                            </div>
                            <div class="col-md-3 col-12 p-0 text-right">
                                <img :src="loader_url" alt="Loading..." v-show="loader">
                                <span v-if="recordContainer.length >= 1"> Selected  <b>{{ recordContainer.length | freeNumber }}</b> of  </span>
                                <span v-else>Showing</span>
                                <span><b>{{ totalNumberOfRecords | freeNumber }}</b> Results<br></span>
                                <span class="float-right"  v-if="recordContainer.length >= 1">
                                    <span class="float-left d-inline-block mr-2 text-center">
                                        <b>{{ recordContainer.length | freeNumber }}</b> Records Selected<br>
                                        <a class="cursor-pointer" style="position:relative;top:-5px" @click="selectAllRecords()"><u>Select All</u></a>
                                    </span>
                                    <img :src="loader_url" v-if="loader == 4">
                                    <div class="dropdown d-inline-block">
                                        <a class="btn btn-outline-danger btn-sm theme-btn dropdown-toggle" href="#" role="button" id="dropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> EXPORT </a>
                                        <div class="dropdown-menu source-dropdown left--65" aria-labelledby="dropdownMenuLink2">
                                            <!-- <a href="#" class="dropdown-item text-uppercase">Export and Sync in Outreach</a>
                                            <a href="#" class="dropdown-item text-uppercase">Export and Sync in Activecampaign</a> -->
                                            <a href="javascript:;" @click="StartExport" class="dropdown-item text-uppercase"> Five9</a>
                                            <hr class="dropdown-divider">
                                            <!-- <a href="#" class="dropdown-item text-uppercase">Export In CSV File</a> -->
                                            <download-excel
                                                class="dropdown-item text-uppercase"
                                                :data="json_data"
                                                :fields="json_fields"
                                                :before-generate = "startDownload"
                                                :before-finish   = "finishdownload"
                                                worksheet="prospect-export"
                                                :name="filename"
                                                type="csv"
                                            >
                                            CSV
                                            </download-excel>
                                        </div>
                                    </div>                            
                                </span>
                            </div>
                        </div>
                    </div> 
                    <div class="divtable">
                        <div class="divthead">
                            <div class="divthead-row">
                                <div class="divthead-elem wf-45 text-center">
                                    <input type="checkbox" name="" id="check-all" value="0" aria-label="..." @click="addAndRemoveAllRecordToContainer">
                                </div>
                                <div class="divthead-elem wf-200">
                                    Name                            
                                </div>
                                <div class="divthead-elem wf-200">
                                    Stage                        
                                </div>
                                <div class="divthead-elem wf-200">
                                    Tags                        
                                </div>
                                <div class="divthead-elem wf-200">
                                    Persona                      
                                </div>
                                <div class="divthead-elem wf-100">
                                    Status
                                </div>
                                <div class="divthead-elem wf-100">
                                    Last Contacted
                                </div>
                            </div>
                        </div>
                        <div class="divtbody  fit-divt-content">
                            <div class="divtbody-row" v-for="(record, i) in records.data" :key="record.id" :class="[(active_row.id == record.record_id)?'expended':'']">
                                <div class="divtbody-elem  wf-45 text-center">
                                    <div class="form-check">
                                        <input :id="'record-'+record.id" class="form-check-input me-1" type="checkbox" :value="record.id" @click="addAndRemoveRecordToContainer(record.id)">
                                    </div>
                                </div>
                                <div class="divtbody-elem wf-200">
                                    <a href="javascript:void(0)" class=""><b>{{ record.first_name }} {{ record.last_name }} </b></a>
                                    <span  v-if="record.stage !== null">
                                        <span class="badge badge-danger" v-if="record.stage == 'Cold / Not Started'">Cold Lead</span>
                                        <span class="badge badge-warning" v-else-if="record.stage == 'Interested'">Warm Lead</span>
                                        <span class="badge badge-success" v-else-if="record.stage == 'Negotiating'">Hot Lead</span>
                                        <span v-else></span>
                                    </span><br>
                                    <small class="fw-500" v-title="record.designation" v-if="record.designation">{{ record.designation }}  in </small> 
                                    <small class="fw-500 border border-light rounded px-2" v-title="record.company" v-if="record.company">{{ record.company }}</small>
                                </div>
                                <div class="divtbody-elem wf-200">
                                    <span v-if="record.stage_data" :class="record.stage_data.css">
                                        {{ record.stage_data.stage }}
                                    </span>
                                    <span class="no-stage" v-else>No Stage</span>
                                </div>                        
                                <div class="divtbody-elem  wf-200">
                                    <span v-if="record.outreach_tag != null">
                                        <a href="javascript:void(0)" v-for="(tag, ti) in (record.outreach_tag.split(','))" v-title="tag" @click="filterTag(tag, 'outreach_tag', 'contains')" :key="'otg'+ti">{{ tag }}</a>
                                    </span>                            
                                </div>
                                <div class="divtbody-elem  wf-200">
                                    <span v-if="record.outreach_persona != null">
                                    <a href="javascript:void(0)" @click="filterTag(record.outreach_persona, 'outreach_persona', 'contains')">{{ record.outreach_persona }}</a>
                                    </span>
                                </div>
                                <div class="divtbody-elem  wf-100">
                                    <span v-if="record.mobilePhones" v-title="record.mobilePhones"><i class="bi bi-telephone-fill px-2"></i></span>
                                    <span v-else><i class="bi bi-telephone px-2"></i></span>
                                    <span v-if="record.email || record.emails" v-title="record.emails?record.emails:record.email">
                                        <i class="bi bi-envelope-fill px-2"></i>
                                    </span>
                                    <span v-else><i class="bi bi-envelope px-2"></i></span>
                                </div>
                                <div class="divtbody-elem  wf-100">
                                    {{ record.outreach_touched_at | convertInDayMonth  }}
                                </div>
                            </div>
                        </div>
                        <div class="divtfoot">
                            <div class="text-center py-1">
                                <span class="form-inline d-inline-flex mr-3">
                                    <label class="form-control  pr-0 border-none"> Show : </label>
                                    <select class="form-control" v-model="form.recordPerPage" @change="getOutreachData(1)">
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="all">All</option>
                                    </select>
                                </span>
                                <pagination :limit="5" :data="records" @pagination-change-page="getOutreachData"></pagination>
                            </div>                    
                        </div>
                    </div>
                </div>
                <div class="row m-0" v-if="step == 1">
                    <div class="col-md-12 col-12 py-2">
                        <span class="d-block">
                            <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 0)?'btn-dark':'btn-secondary']" @click="showrecords = 0">All Records [{{sform.fdata.length }}]</button>          
                            <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 1)?'btn-dark':'btn-secondary']" @click="showrecords = 1">Unique Records [{{ unique_Records.length }}]</button>          
                            <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 2)?'btn-dark':'btn-secondary']" @click="showrecords = 2">Duplicate Records [{{ drcount }}]</button>     
                            <button type="button" class="float-right btn btn-sm btn-primary theme-btn icon-btn" @click="checkRecords">Next <i class="bi bi-arrow-right"></i></button> 
                            <a href="/universal-dashboard" class="float-right btn btn-sm btn-success mr-2 theme-btn icon-btn"><i class="bi bi-arrow-left"></i> Back </a>
                        </span>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed" v-if="(showrecords == 0) || (showrecords == 1 && sform.fdata.length == unique_Records.length)">
                                <thead>
                                    <tr>
                                        <th>SNo</th>
                                        <th>Name</th>
                                        <th>Number1</th>
                                        <th>Number2</th>
                                        <th>Number3</th>
                                        <th>Company</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(record, rkey) in sform.fdata" :key="'all-'+rkey">
                                        <td>{{ rkey+1 }}</td>
                                        <td>{{ record.first_name+' '+record.last_name }}</td>
                                        <td>{{ record.number1 }}</td>
                                        <td>{{ record.number2 }}</td>
                                        <td>{{ record.number3 }}</td>
                                        <td>{{ record.company }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" type="button" @click="deleteRecord(rkey)">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-striped table-condensed" v-if="showrecords == 1 && sform.fdata.length > unique_Records.length">
                                <thead>
                                    <tr>
                                        <th>SNo</th>
                                        <th>Name</th>
                                        <th>Number1</th>
                                        <th>Number2</th>
                                        <th>Number3</th>
                                        <th>Company</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(record, rkey) in unique_Records" :key="'unique-'+rkey">
                                        <td>{{ rkey+1 }}</td>
                                        <td>{{ record.first_name+' '+record.last_name }}</td>
                                        <td>{{ record.number1  }}</td>
                                        <td>{{ record.number2  }}</td>
                                        <td>{{ record.number3  }}</td>
                                        <td>{{ record.company }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" type="button" @click="deleteUnRecord(record.number1)">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <span v-else-if="(showrecords == 2) && (drcount >= 1)">
                                <table class="table table-condensed table-striped" v-for="(groupedRecord, index) in duplicate_Records" :key="'row'+index">                    
                                    <thead>
                                        <tr>
                                            <th class="wf-80">SNo</th>
                                            <th class="wf-180">Name</th>
                                            <th colspan="3" class="wf-300">Duplicate Number1:  {{ groupedRecord[0].number1 }}</th>
                                            <th class="wf-100">Company</th>
                                            <th width="wf-100">Action</th>
                                        </tr>
                                    </thead>                    
                                    <tbody>
                                        <tr v-for="(record,sno) in groupedRecord" :key="'in-row-'+sno">   
                                            <td class="wf-80">{{ sno+1}} </td>                      
                                            <td class="wf-180">{{ record.first_name +' '+ record.last_name }}</td>
                                            <td class="wf-100">{{ record.number1 }} </td>
                                            <td class="wf-100">{{ record.number2 }}</td>
                                            <td class="wf-100">{{ record.number3 }}</td>
                                            <td class="wf-100">{{ record.company }}</td>
                                            <td class="wf-100">
                                                <button class="btn btn-sm btn-danger mr-1" type="button" @click="deleteGroupRecords(record.number1, record.number2, record.number3, record.first_name)"><i class="bi bi-x"></i> </button>
                                                <button  class="btn btn-sm btn-success" type="button" @click="swapRecords(record.number1, record.number2, record.number3)"><i class="bi bi-arrow-down-up"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </span>
                            <p class="alert alert-info" v-else-if="(duplicate_Records.length == 0) && (showrecords == 2)">
                                No duplicate records found.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row m-0" v-else-if="step == 2">
                    <div class="col-sm-12 text-center p-3">
                        <h4>Syncing Started</h4>
                        <p class="alert alert-info">
                            <b> {{ unique_Records.length + duplicate_Records.length }} unique records will be imported. </b>
                        </p>
                    </div>
                    <div class="col-md-3 col-12">
                        
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group" v-if="sform.destination == 'activecampaign'">
                            <label for="form_name">Enter List Name</label>
                            <input type="text" name="form_name" id="form_name" class="form-control" v-model="sform.name">
                            <i class="text-warning">Keep this field empty if you do not want to create new list</i>
                        </div>
                        <div v-else-if="sform.destination == 'five9'">
                            <div class="form-group">
                                <label for="form_name">Select List</label>
                                <select required name="lid" id="lid" class="form-control" v-model="sform.lid">
                                    <option value="">Select List</option>
                                    <option value="0">Create New List</option>
                                    <option :value="lname.name" v-for="(lname, lk) in five9_list" :key="'listid'+lk">
                                        {{ lname.name }} ({{ lname.size }})
                                    </option>
                                </select>
                            </div>
                            <div class="form-group" v-show="sform.lid == '0'">
                                <label for="form_name">Enter List Name</label>
                                <input type="text" required name="form_list_name" id="form_list_name" class="form-control" v-model="sform.name">
                            </div>
                            <div class="form-group">
                                <label for="form_name">Select Campaign</label>
                                <select required name="cid" id="cid" class="form-control" v-model="sform.cid">
                                    <option value="">Select Campaign</option>
                                    <option value="0">Create New Campaign</option>
                                    <option :value="cname.name" v-for="(cname, ck) in five9_ocampaigns" :key="'campaignid'+ck">
                                        {{ cname.name }} ({{ cname.mode }}) - {{  cname.state }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group" v-show="sform.cid == '0'">
                                <label for="form_name">Enter Campaign Name</label>
                                <input type="text" required name="form_Campaign_name" id="form_Campaign_name" class="form-control" v-model="sform.campaign">
                            </div>
                        </div>
                        <img :src="loader_url" v-if="loader == 4">
                        <button class="btn theme-btn btn-sm btn-primary" v-else type="button" @click="startSyncing"> Start Uploading</button>
                        <p class="text-danger"> {{errornote }}</p>
                    </div>
                    <div class="col-md-3 col-12">
                        
                    </div>
                </div>
                <div class="m-0" v-else-if="step == 3">
                    <h5 class="p-4 text-center text-dark">Import process has been completed successfully.<br>
                        {{ report }}
                    </h5>
                    <h5 class="p-4 text-center text-dark">
                        <a href="/universal-dashboard">Click Here</a> to go to Dashboard
                    </h5>
                </div>
                <div class="modal" tabindex="-1" role="dialog" id="addView">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Save New View</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <label for="view-name-modal">Name</label>
                            <input  id="view-name-modal" type="text" class="form-control" v-model="form.viewName" placeholder="Enter view name">
                            <label for="view-sharing-model">Sharing</label>
                            <select id="view-sharing-model" v-model="form.sharing" class="form-control" >
                                <option value="private">Private to owner</option>
                                <option value="public">Available to all</option>
                            </select>
                            <label for="view-applied-filter-model">Applied Filter</label>
                            <span v-for="(filter) in form.filterConditionsArray" :key="'filter-'+filter.conditionText+'-'+filter.formula+'-'+filter.textCondition">
                                <span class="badge bg-primary p-2 m-1 pointer-hand"> {{filter.textConditionLabel }}</span>
                                
                            </span>
                            <label for="view-sorted-by">Sorted By</label><br>
                            <span>{{ form.sortType | getStringWithSpace }}</span>
                            - 
                            <span v-if="form.sortBy == 'desc'">Descending</span>
                            <span v-else>Ascending</span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" @click="saveView">Save</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 p-3">
                <div class="text-center">
                    
                    <img :src="accountImage" class="rounded-circle" alt="Cinque Terre" style="height:70px;"> 
                    <h3>{{ accountInfo.name }}</h3>
                    <p>@{{ accountInfo.websiteUrl }}</p>
                    <p>
                        <a :href="'http://'+accountInfo.websiteUrl" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link" viewBox="0 0 16 16">
                                <path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9c-.086 0-.17.01-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/>
                                <path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4.02 4.02 0 0 1-.82 1H12a3 3 0 1 0 0-6H9z"/>
                            </svg>
                        </a>
                        <a :href="accountInfo.linkedInUrl" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16">
                                <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/>
                            </svg>
                        </a>
                    </p>
                </div>
                OVERVIEW:
                <p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                        <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                    </svg> <router-link :to="'/accounts/' + accountId" class="">{{ this.totalRecords }} Prospects</router-link></p>
                
                <p> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stopwatch" viewBox="0 0 16 16">
                        <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5V5.6z"/>
                        <path d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64a.715.715 0 0 1 .012-.013l.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354a.512.512 0 0 1-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5zM8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3z"/>
                    </svg> Last contacted :  {{ accountInfo.touchedAt | convertInDayMonth}}</p>
                OWNER 
                <p>: {{ accountInfo.owner }}</p>
                TAGS : 
                <p> {{ accountInfo.tags }}</p>
                ABOUT :
                <p>  {{ accountInfo.description }}</p>
                OVERVIEW:
                <p>Industry : {{ accountInfo.industry }}</p>

            </div>
        </div>
    </div>
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import { ToggleButton } from 'vue-js-toggle-button';
import downloadExcel from "vue-json-excel";
import 'vue-select/dist/vue-select.css';

export default {
    components: { DateRangePicker, ToggleButton, downloadExcel},
    data() {
        return {
            accountInfo : [],
            accountId : '',
            accountImage : '/img/account.png',
            filter_expand:true,
            stageDetails:[],
            totalNumberOfRecords : 0,
            loader:false,
            view:'list',
            step:0,
            page:{page:1,start:1,end:1,count:1,total:1,pager:1},
            paginationArray: {},
            records:{},
            start : 0,
            end : 0,
            totalRecords : 0,
            allProspects:{},
            active_row:{
                id:'',
                type:''
            },
            form: new Form({
                account_id : '',
                sortType:'outreach_touched_at',
                sortBy:'desc',
                dateRange:{},
                recordPerPage:10,
                outreach:1,
                activecampaign:0,
                five9:0,
                page:1,
                stage:'all',
                textSearch:'',
                filter:'',
                filterType:'',
                dropdown: '',
                filterOption:'is',
                filterText:'',
                filterConditionsArray : [],
                viewName : '',
                sharing : 'private',
            }),
            sform: new Form({
                template_id:'',
                source:'',
                destination:'five9',
                fdata:[],
                report:'',
                fields:['record_id','firstName','lastName','email','mobilePhones','workPhones','homePhones','stage','state','address','outreach_tag','zip','city','company'],
                efields:[],
                records:'',
                name:'',
                campaign:'',
                lid:'',
                cid:''
            }),
            export_data:[],
            outreach:'outreach.png',
            five9:'five9-light.png',
            activecampaign:'activecampaign2.png',
            all:'round.png',
            calls:{},
            emails:{},
            callDispositions:{},
            callPurposes:{},
            recordContainer:[],
            emailDelivered:0,
            emailOpened:0,
            emailClicked:0,
            emailReplied:0,
            owner : 0,
            options: [],
            climit:'',
            templates:{},
            export_data:[],
            channel_name: '',
            channel_fields: [],
            channel_entries: [],
            five9_list:'',
            five9_campaigns:[],
            report:'',
            loader_url: '/img/spinner.gif',
            testing:[],
            groupedRecords:[],
            showDuplicate:true,
            showUnique:true,
            duplicateRecordCounter:0,
            uniqueRecordCounter:0,
            activities: [],
            mailingList:[],
            activityMeta:'',
            mapped_ranges:[],
            json_fields: {
                "Record Id" : "record_id",
                "First Name": "first_name",
                "Last Name": "last_name",
                "Number 1" : "mobilePhones",
                "Number 2" : "workPhones",
                "Number 3" : "homePhones",
                Company : "company",
                Street : "address",
                City : "city",
                State : "state",
                Zip : "zip",
                Emails: "emails",
                "Personal Notes" : "personal_note",
                "Dial Attempts" : "dial_attempts",
                "Last Agent" : "last_agent",
                "Last Agent Dispo Timestamp" : "last_agent_dispo_time",
                "Last Dispo" : "last_dispo",
                Tag : "outreach_tag",
                Stage : "stage"
            },
            json_data: [],
            json_meta: [
                [
                    {
                    key: "charset",
                    value: "utf-8",
                    },
                ],
            ],
            filename: '',
            reports : {
                header : {
                    values : {}
                }
            },
            paginationRecordPerPage:20,
            filter : false,
            filterInput : false,
            filterDropdown : false,
            filterDateRange : false,
            filterItems : [],
            filterOptions : [],
            selects : [],
            filterConditionsObject : {},            
            filterConditionsArrayOld : [],
            filterBtn : true,
            views : [],
            view : '',
            showView : false, //control appearance of view controls
            addView:false,
            selectedOptions : [],
            selectedOptionsId : [],
            showrecords:0,
            unique_Records: [],
            duplicate_Records: [],
            groupedRecords : [],
            groupKey:[],
            filterItemsIds : [],
        }
    },
    filters: {
        capitalize: function (str) {
            return str.charAt(0).toUpperCase() + str.slice(1)
        },
        titleFormat: function (str) {
            let nstr = str.charAt(0).toUpperCase() + str.slice(1);
            return nstr.replace('_', ' ');
        },
        setTime: function(params) {
             return params;
        } ,
        phoneFormatted: function (str) {
            if(str != null && str != 0 && str != 'undefined' && str != '') {
                console.log(str);
                let fst_str = str.substr(0, 1);
                const regex = / /gi;
                str = str.replace(regex, '');
                if(fst_str == '+') {
                    str = str.substr(1, str.length-1);
                }
                fst_str = str.substr(0, 1);
                if(fst_str == 1) {
                    str = str.substr(1, str.length-1);
                }
                str = str.substr(0, 10);
                if(str.length != 10) {
                    return 0;
                } else {
                    return parseInt(str);
                }
            } else {
                return 0;
            }
        },
    },
    computed: {
        ac_options() {
            let acf = this.$store.getters.currentConfig.activecampaign_fields;
            return acf.split(',');
        },
        or_options() {
            let acf = this.$store.getters.currentConfig.outreach_fields;
            return acf.split(',');
        },
        f9_options() {
            let acf = this.$store.getters.currentConfig.five9_fields;
            return acf.split(',');
        },
        five9_ocampaigns () {
            return this.five9_campaigns.filter((cam) => { return cam.type == 'OUTBOUND' });
        },
        drcount() {
            return this.sform.fdata.length - this.unique_Records.length;
        }
    },
    methods: {
        removeSelectedOption(index){
            this.selectedOptions.splice(index,1)
            this.selectedOptionsId.splice(index,1)
        },
        getSelectedOptions(){            
            var id = this.form.dropdown
            var data = this.selects.filter(function(e){
                return e.oid == id
            })
            if(this.selectedOptions.indexOf(data[0].stage) == -1){
                this.selectedOptions.push(data[0].stage)
                this.selectedOptionsId.push(data[0].oid)
            }
        },
        selectView(){
            this.filter = false
            if(this.view == null){
                this.form.sortType = 'outreach_touched_at';
                this.form.sortBy = 'asc';
                this.form.dateRange = {};
                this.form.recordPerPage = 20;
                this.form.outreach = 1;
                this.form.activecampaign = 0;
                this.form.five9 = 0;
                this.form.page = 1;
                this.form.stage = 'all';
                this.form.textSearch = '';
                this.form.filter = '';
                this.form.filterType = '';
                this.form.dropdown = ''
                this.form.filterOption = 'is';
                this.form.filterText = '';
                this.form.filterConditionsArray = [];
                this.form.viewName = '';
            }else{
                var view = this.view.id
                var currentView = this.views.filter(function(e){
                    return e.id == view
                });
                var obj = JSON.parse(currentView[0].view_data);
                this.form.sortType = obj.sortType
                this.form.sortBy = obj.sortBy
                this.form.dateRange = {}
                this.form.recordPerPage = obj.recordPerPage
                this.form.outreach = obj.outreach
                this.form.activecampaign = obj.activecampaign
                this.form.five9 = obj.five9
                this.form.page = 1
                this.form.stage = obj.stage
                this.form.textSearch = obj.textSearch
                this.form.filter = obj.filter
                this.form.filterType = obj.filterType
                this.form.dropdown = obj.dropdown
                this.form.filterOption = obj.filterOption
                this.form.filterText = obj.filterText
                this.form.filterConditionsArray = obj.filterConditionsArray
            }
            var items = this.form.filterConditionsArray.filter(function(e) {
                return e
            }).map(function(e){
                return e.condition
            })
            console.log(this.filterItemsAll)
            this.filterItemsIds = this.filterItemsAll.filter(function(e){
                if(items.indexOf(e.filter_key) !== -1){
                    return e
                }
            }).map(function(e){
                return e.id
            })
            var filterItemsIds = this.filterItemsIds
            this.filterItems = this.filterItems.filter(function(e){
                if(filterItemsIds.indexOf(e.id) !== -1){
                    return e
                }
            })
            this.getOutreachData(1)
        },
        showViewPopup() {
            $('#addView').modal('show');
        },
        saveView(){
            if(this.form.viewName == ''){
                this.$toasted.show("Please enter name!!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    duration : 2000
                });
            } else {
                this.form.post('/api/save-view').then((response) => {
                    this.$toasted.show("View saved successfully!!", { 
                        theme: "bubble", 
                        position: "bottom-center", 
                        duration : 2000
                    });
                    this.getallview();
                    $('#addView').modal('hide');
                    this.form.viewName = '';
                    this.showView = false;
                    this.view = response.data.results;
                    
                })
            }
        },
        removeFilter(index){
            this.form.filterConditionsArray.splice(index, 1)
            this.getOutreachData(1);
        },
        updateFilter(){            
            for(const i in this.form.filterConditionsArray){
                if(this.form.filterConditionsArray[i]["condition"] == this.form.filter){
                    if(this.form.filterConditionsArray[i]["type"] == "textbox"){

                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        this.form.filterConditionsArray[i]["textCondition"] = this.form.filterText

                    }
                    if(this.form.filterConditionsArray[i]["type"] == "dropdown"){

                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        this.form.filterConditionsArray[i]["textCondition"] = this.selectedOptionsId.join(',')            
                        this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +' '+ this.form.filterOption +' '+ this.selectedOptions.join(', ')
                    }
                    if(this.form.filterConditionsArray[i]["type"] == "calendar"){

                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        this.form.filterConditionsArray[i]["textCondition"] = this.form.filterText
                        var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                        var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                        this.form.filterConditionsArray[i]['textCondition'] = startDate+'--'+endDate
                        this.form.filterConditionsArray[i]['textConditionLabel'] = startDate.substring(0,10)                        
                    }
                    this.form.filterText = ''
                    this.form.filter = ''
                    this.form.dropdown = ''
                    this.filter = false
                    this.filterInput = false
                    this.filterDropdown = false
                    this.filterDateRange = false
                    this.filterBtn = true
                    this.getOutreachData(1)
                }
            }
        },
        showFilterDetails(filter, index){
            if(filter.type == 'textbox'){
                this.form.filter = filter.condition
                this.form.filterOption = filter.formula
                this.form.filterText = filter.textCondition
                this.filter = true
                this.filterInput = true
                this.filterDropdown = false
                this.filterDateRange = false
            }
            if(filter.type == 'dropdown'){
                this.form.filter = filter.condition
                this.form.filterOption = filter.formula
                this.form.dropdown = filter.textCondition
                this.filter = true
                this.filterInput = false
                this.filterDropdown = true
                this.filterDateRange = false
                let api = filter.api
                axios.get(api).then((response) => {
                    this.selects = response.data.results;
                });
                this.selectedOptions = []
                this.selectedOptionsId = []
                var newRecords = filter.textCondition.split(",")
                for(var i = 0; i <= newRecords.length; i++){
                    var newStage = this.selects.filter(function(e){
                        return e.oid == newRecords[i]
                    })
                    if(newStage.length > 0){
                        this.selectedOptionsId.push(newRecords[i])
                        this.selectedOptions.push(newStage[0].stage)
                    }
                }
            }
            if(filter.type == 'calendar'){
                this.form.filter = filter.condition
                this.form.filterOption = filter.formula
                this.form.filterText = filter.textCondition
                this.filter = true
                this.filterInput = false
                this.filterDropdown = false
                this.filterDateRange = true
            }
            this.filterBtn = false            
        },
        createFilter(){           
            if(this.form.filter.filter_type == 'textbox'){
                var textCondition = this.form.filterText  
                var textConditionLabel =this.form.filter.filter +' '+ this.form.filterOption +' '+ this.form.filterText
                var api = '';
            }
            if(this.form.filter.filter_type == 'calendar'){
                    var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                    var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                    var textCondition = startDate+'--'+endDate
                    var textConditionLabel = this.form.filter.filter +' '+ startDate.substring(0,10)+ ' to ' + endDate.substring(0,10)
                    var api = '';
            }
            if(this.form.filter.filter_type == 'dropdown'){

                var formdropdown = this.form.dropdown
                var textCondition = this.selectedOptionsId.join(',')
                var textConditionLabel = this.form.filter.filter +' '+ this.form.filterOption +' ' + this.selectedOptions.join(', ')
                var api = this.form.filter.api
            }
            this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                'type' : this.form.filter.filter_type,
                'condition' : this.form.filter.filter_key,
                'conditionText' : this.form.filter.filter,
                'formula' : this.form.filterOption,
                'textCondition' : textCondition ,
                'textConditionLabel' : textConditionLabel,
                'api' : api,
            }   
            this.filterItemsIds[this.filterItemsIds.length] = this.form.filter.id                 
            this.form.filterText = ''
            this.form.filter = ''
            this.form.dropdown = ''
            this.filter = false
            this.filterInput = false
            this.filterDropdown = false
            this.filterDateRange = false
            this.getOutreachData(1)
        },
        showFilterOption(){
            if(this.form.filter == null){
                this.filterInput = false;
                this.filterDropdown = false;
                this.filterDateRange = false;
            }else{
                this.filterOptions = this.form.filter.filter_option.split(',')
                if(this.form.filter.filter_type == 'textbox'){
                    this.filterInput = true;
                    this.filterDropdown = false;
                    this.filterDateRange = false;
                    var oldData = this.form.filterConditionsArray.filter(function(e){
                        return ((e.type == filterType) && (e.condition == filterKey))
                    })
                    if(oldData.length > 0){                        
                        this.showFilterDetails(oldData[0], 1)
                    }
                }
                if(this.form.filter.filter_type == 'calendar'){
                    this.filterInput = false;
                    this.filterDropdown = false;
                    this.filterDateRange = true;
                }
                if(this.form.filter.filter_type == 'dropdown'){                    
                    var filterType = this.form.filter.filter_type
                    var filterKey = this.form.filter.filter_key
                    var oldData = this.form.filterConditionsArray.filter(function(e){
                        return ((e.type == filterType) && (e.condition == filterKey))
                    })
                    if(oldData.length > 0){                        
                        this.showFilterDetails(oldData[0], 1)
                    }
                    this.selects = []
                    this.filterInput = false;
                    this.filterDropdown = true;
                    this.filterDateRange = false;
                    let api = this.form.filter.api
                    axios.get(api).then((response) => {
                        this.selects = response.data.results;
                    });
                }
            }
        },
        showFilter(){
            axios.get('/api/get-all-filter').then((response) => {
                this.filterItems = response.data.items
                this.filterItemsAll = response.data.items
                this.filter = true
                var filterItemsIds = this.filterItemsIds
                //remove filter item form filter-item array
                if(filterItemsIds.length > 0){
                    this.filterItems = this.filterItems.filter(function(e) {
                        if(filterItemsIds.indexOf(e.id) == -1){
                            return e
                        }
                    })
                }
            });
        },
        reset () {
            this.item = {}
        },
        selectFromParentComponent1 () {
            // select option from parent component
            this.item = this.options[0]
        },
        filterStage(sid) {
            if(sid == 'all') {
                this.form.stage = 'all';
                var data = this.form.filterConditionsArray.filter(function(e){
                    return e.condition != "stage"
                })
                this.form.filterConditionsArray = []
                this.form.filterConditionsArray = data;
                this.getFilterData();
            }
            else {
                this.form.stage = sid;
                var stage = this.stageDetails.filter(function(e){
                return e.oid == sid
                })
                axios.get('/api/get-all-filter').then((response) => {
                    this.filterItems = response.data.items
                    var stageDetail = this.filterItems.filter(function(e){
                        return e.filter_key == "stage"
                    })
                    if(this.form.filterConditionsArray.length == 0){

                    } else {
                        var data = this.form.filterConditionsArray.filter(function(e){
                            return e.condition != "stage"
                        })
                        this.form.filterConditionsArray = []
                        this.form.filterConditionsArray = data
                    }
                    this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                        'type' : stageDetail[0].filter_type,
                        'condition' : stageDetail[0].filter_key,
                        'conditionText' : stageDetail[0].filter,
                        'formula' : "is",
                        'textCondition' : sid ,
                        'textConditionLabel' : stageDetail[0].filter +' is '+ stage[0].stage,
                        'api' : stageDetail[0].api
                    }     
                    this.getFilterData();
                });
            }
        },
        filterTag(filed, fieldName, formula) { // like tag, outrech_tag, is contain
            axios.get('/api/get-all-filter').then((response) => {
                this.filterItems = response.data.items
                var fieldDetail = this.filterItems.filter(function(e){
                    return e.filter_key == fieldName
                })
                if(this.form.filterConditionsArray.length == 0){

                } else {
                    var data = this.form.filterConditionsArray.filter(function(e){
                        return e.condition != fieldName
                    })
                    this.form.filterConditionsArray = []
                    this.form.filterConditionsArray = data
                }
                this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                    'type' : fieldDetail[0].filter_type,
                    'condition' : fieldDetail[0].filter_key,
                    'conditionText' : fieldDetail[0].filter,
                    'formula' : formula,
                    'textCondition' :  filed,
                    'textConditionLabel' : fieldDetail[0].filter +' '+ formula +' '+ filed,
                    'api' : fieldDetail[0].api
                }     
                this.getFilterData(1);
            });
        },
        
        pageReset() {
            window.location.reload();
        },
        StartExport() {
            this.loader = true;
            this.step = 1;
            this.sform.destination = 'five9';
            axios.get('/api/get-f9-list').then((response) => {
                this.five9_list = response.data;
            });
            axios.get('/api/get-f9-campaigns').then((response) => {
                this.five9_campaigns = response.data;
            });
            let opts = this.f9_options;
            let vm = this;
            opts.forEach(function (key) {
                vm.sform.efields.push(key);
            });
            //this.sform.records = this.recordContainer;
            this.makingTestRecords();
            this.makingGroupedRecords();
        },
        makingTestRecords(){
            for(var i = 0; i < this.recordContainer.length; i++){    
                
                let mobilePhones = 0;
                if(this.allProspects[this.recordContainer[i]].mobilePhones){
                    mobilePhones = this.allProspects[this.recordContainer[i]].mobilePhones;                   
                    mobilePhones = mobilePhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    mobilePhones = mobilePhones.replace("-", '')
                    mobilePhones = mobilePhones.substring(0,10)
                    if(mobilePhones.length == 10){
                        mobilePhones = parseInt(mobilePhones)
                    }else{
                        mobilePhones = 0;
                    }
                }
                let workPhones = 0;
                if(this.allProspects[this.recordContainer[i]].workPhones){
                    workPhones = this.allProspects[this.recordContainer[i]].workPhones;                   
                    workPhones = workPhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    workPhones = workPhones.replace("-", '')
                    workPhones = workPhones.substring(0,10)
                    if(workPhones.length == 10){
                        workPhones = parseInt(workPhones)
                    }else{
                        workPhones = 0;
                    }
                }
                let homePhones = 0;
                if(this.allProspects[this.recordContainer[i]].homePhones){
                    homePhones = this.allProspects[this.recordContainer[i]].homePhones;                   
                    homePhones = homePhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    homePhones = homePhones.replace("-", '')
                    homePhones = homePhones.substring(0,10)
                    if(homePhones.length == 10){
                        homePhones = parseInt(homePhones)
                    }else{
                        homePhones = 0;
                    }
                }
                
                
                if(this.allProspects[this.recordContainer[i]].first_name){
                    var firstName = this.allProspects[this.recordContainer[i]].first_name;
                }else{
                    var firstName = null;
                }
                if(this.allProspects[this.recordContainer[i]].last_name){
                    var lastName = this.allProspects[this.recordContainer[i]].last_name;
                }else{
                    var lastName = null;
                }
                
                if(this.allProspects[this.recordContainer[i]].state){
                    var addressState = this.allProspects[this.recordContainer[i]].state;
                }else{
                    var addressState = null;
                }
                if(this.allProspects[this.recordContainer[i]].address){
                    var addressStreet = this.allProspects[this.recordContainer[i]].address;
                    addressStreet = addressStreet.replace(/[,]/g, "")
                }else{
                    var addressStreet = null;
                }
                if(this.allProspects[this.recordContainer[i]].outreach_tag){
                    
                    var tags = this.allProspects[this.recordContainer[i]].outreach_tag;
                }else{
                    var tags = null;
                }
                if(this.allProspects[this.recordContainer[i]].zip){
                    var addressZip = this.allProspects[this.recordContainer[i]].zip;
                }else{
                    var addressZip = null;
                }
                if(this.allProspects[this.recordContainer[i]].city){
                    var addressCity = this.allProspects[this.recordContainer[i]].city;
                }else{
                    var addressCity = null;
                }
                if(this.allProspects[this.recordContainer[i]].company){
                    var company = this.allProspects[this.recordContainer[i]].company;
                }else{
                    var company = null;
                }
                if(this.allProspects[this.recordContainer[i]].record_id){
                    var record_id = this.allProspects[this.recordContainer[i]].record_id;
                }else{
                    var record_id = null;
                }
                let emails = null;
                if(this.allProspects[this.recordContainer[i]].emails){
                    emails = this.allProspects[this.recordContainer[i]].emails
                    if(emails.search(',') > 0){                         
                        emails = emails.substring(0,emails.search(','));
                    } else {
                        emails = this.allProspects[this.recordContainer[i]].emails
                    }
                }
                if(this.allProspects[this.recordContainer[i]].stage_data){
                    var stage = this.allProspects[this.recordContainer[i]].stage_data.stage;
                }else{
                    var stage = null;
                }              
                //['first_name','last_name','email','number1','number2','number3','Stage','state','street','tag','zip','city','company'],
                let n1, n2, n3;
                if(mobilePhones == 0 && workPhones != 0  && homePhones != 0) {
                    n1 = workPhones;
                    n2= homePhones;
                    n3 = 0;
                } 
                else if(mobilePhones == 0 && workPhones == 0 && homePhones != 0) {
                    n1 = homePhones;
                    n2= 0;
                    n3 = 0;
                } 
                else if(mobilePhones == 0 && workPhones != 0 && homePhones == 0) {
                    n1 = workPhones;
                    n2= 0;
                    n3 = 0;
                } 
                else {
                    n1 = mobilePhones;
                    n2= workPhones;
                    n3 = homePhones;
                }
                console.log(mobilePhones+' - '+workPhones+' - '+homePhones);
                this.sform.fdata[i] = {
                    "record_id" : record_id,
                    "first_name" : firstName,
                    "last_name" : lastName,
                    "number1" : n1,
                    "number2" : n2,
                    "number3" : n3,
                    "state" : addressState,
                    "Stage" : stage,
                    "street": addressStreet,
                    "tag" : tags,
                    "zip" : addressZip,
                    "city" : addressCity,
                    "company" : company,
                    "email" : emails,
                }
                // this.testing[i] = {
                //     "firstName" : firstName,
                //     "lastName" : lastName,
                //     "mobilePhones" : mobilePhones,
                //     "homePhones" : homePhones,
                //     "workPhones" : workPhones,
                //     "addressState" : addressState,
                //     "Stage" : stage,
                //     "addressStreet": addressStreet,
                //     "tags" : tags,
                //     "addressZip" : addressZip,
                //     "addressCity" : addressCity,
                //     "company" : company,
                //     "email" : emails,
                // }
            }
        },
        makingGroupedRecords(){
            this.unique_Records = [];
            this.duplicate_Records = [];
            let gkey = this.sform.fdata.reduce((acc, it) => {
                acc[it.number1] = acc[it.number1] + 1 || 1;
                return acc;
            }, {});
            this.groupKey = gkey;
            let gk = this;
            this.sform.fdata.forEach(element => {
                if(gk.groupKey.hasOwnProperty(element.number1) && gk.groupKey[element.number1] == 1) {
                    gk.unique_Records.push(element);
                }
            });
            Object.keys(gkey).forEach(function(key) {
                if(gkey[key] >= 2) {
                    let grp = gk.sform.fdata.filter(rec => { return rec.number1 == key });
                    gk.duplicate_Records.push(grp);
                }
            });
        },
        addAndRemoveAllRecordToContainer(){
            var a = document.getElementById('check-all');
            var aa = document.querySelectorAll("input[type=checkbox]");
            if(document.getElementById("check-all").checked == true){
                for (var i = 0; i < aa.length; i++){
                    if(parseInt(aa[i].value) > 1){
                        if((this.recordContainer.indexOf(parseInt(aa[i].value)) == -1)){
                            aa[i].checked = true;
                            this.recordContainer.push(Number(aa[i].value));
                        }else{
                            aa[i].checked = true;
                        }
                    }
                }
            }
            if(document.getElementById("check-all").checked == false){
                var record = [];
                for (var i = 0; i < aa.length; i++){
                    if(Number(aa[i].value) > 0){
                        if((this.recordContainer.indexOf(parseInt(aa[i].value)) >= 0)){
                            this.recordContainer.splice(this.recordContainer.indexOf(parseInt(aa[i].value)), 1);
                            aa[i].checked = false;
                        }
                    }
                }
            }
        },
        addAndRemoveRecordToContainer(id){
            if((this.recordContainer.indexOf(parseInt(id)) == -1) && (document.getElementById("record-"+id).checked == true)){
                this.recordContainer.push(id);
            }else{
                this.recordContainer.splice(this.recordContainer.indexOf(parseInt(id)), 1);
            }
        },       
        updateSorting(by) {
            this.form.sortBy = by;
            this.getOutreachData(1);
        },
        showActivity(rid, rtype, recordId) { 
            this.loader = 5;
            if(rtype == 1)
            {
                axios.get('/api/get-outreach-prospect-activities/'+recordId).then((response) => {
                    this.activities = response.data.details.data;
                    this.mailingList = response.data.details.included;
                    this.activityMeta = response.data.details.meta;
                });
                axios.get('/api/get-outreach-prospect-details-calls/'+recordId).then((response) => {
                    this.calls = response.data.details.data;
                    this.callDispositions = response.data.callDispositionArray;
                    this.callPurposes = response.data.callPurposeArray;

                });
                this.emails = {};
                this.emailDelivered = 0;
                this.emailOpened = 0;
                this.emailClicked = 0;
                this.emailReplied = 0;
                axios.get('/api/get-outreach-prospect-details-emails/'+recordId).then((response) => {
                    this.emails = response.data.details.data;
                            this.emailDelivered = 0;
                            this.emailOpened = 0;
                            this.emailClicked = 0;
                            this.emailReplied = 0;
                    for (let i = 0; i < this.emails.length; i++) {
                        if(this.emails[i].attributes.deliveredAt){
                            this.emailDelivered +=1;     
                        }
                        if(this.emails[i].attributes.openedAt){
                            this.emailOpened +=1;     
                        }
                        if(this.emails[i].attributes.clickedAt){
                            this.emailClicked +=1;     
                        }
                        if(this.emails[i].attributes.repliedAt){
                            this.emailReplied +=1;     
                        }
                    }
                    this.loader = false;
                });
            }       
            if(rtype == 3){
                
            }      
            $('.addiv').hide();
            $('.divtbody-additional').hide();
            this.active_row.id = recordId;
            this.active_row.type = rtype;
            $('#act'+rtype+rid).show();
            $('#action'+rid).show();
        },
        closeActivity() {
            this.active_row.id = '';
            this.active_row.type = '';
            $('.addiv').hide();
            $('.divtbody-additional').hide();
        },
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        changeView() {
            if(this.view == 'list') {
                this.view = 'graph';
            } else {
                this.view = 'list';
            }
        },
        updateType(typ) {
            if(this.form.types.indexOf(typ) == -1) {
                this.form.types.push(typ);
            } else {
                this.form.types.splice(this.form.types.indexOf(typ), 1);
            }
        },
        getFilterData(){
            this.form.sortType = 'outreach_touched_at'
            this.form.sortBy = 'asc'
            this.totalNumberOfRecords = '-';    
            this.getOutreachData(1);
        },
        getOutreachData(pno) {
            var path = this.$route.path.split("/")
            var id = path[path.length-1]
            this.form.account_id = path[path.length-1]
            this.loader = true;
            this.form.page = pno
            document.getElementById("check-all").checked = false;
            this.$Progress.start();            
            this.form.post('/api/get-account-prospects').then((response) => {                
                this.records = response.data.results;
                this.totalRecords = response.data.page.total;                
                this.$Progress.finish();
                let newRecords = response.data.results.data;
                for(const key in newRecords){
                    this.allProspects[newRecords[key]['id']] = newRecords[key];
                }                
                this.totalNumberOfRecords = response.data.page.total;
                this.loader = false;
            });
        },        
        backStep(){
            this.step = 1;
        },
        selectAllRecords()
        {
            this.loader = true;
            this.$Progress.start();
            let query = '';
            query = query + 'stage='+this.form.stage
            if(this.form.dateRange.startDate){
                var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                query = query + "&startDate="+startDate+'&endDate='+endDate
            }
            if(this.form.textSearch){
                query = query + '&textSearch='+this.form.textSearch
            }            
            query = query + '&sort='+this.form.sortType+'&sortby='+this.form.sortBy+'&page='+1
            axios.get('/api/get-outreach-all-prospects-to-export?'+query).then((response) => {
                var aa = document.querySelectorAll("input[type=checkbox]");
                document.getElementById('check-all').checked = true;
                for (var i = 0; i < aa.length; i++){
                    if(parseInt(aa[i].value) > 1){
                        aa[i].checked = true;
                    }
                }                
                let newRecords = response.data.results;
                this.allProspects = {};
                this.recordContainer = []
                for(const key in newRecords){
                    this.allProspects[newRecords[key]['id']] = newRecords[key];
                    this.recordContainer.push(newRecords[key]['id']);                    
                }
                this.totalNumberOfRecords = response.data.totalRecords;   
                this.$Progress.finish();  
                this.loader = false;        
            });
        },
        startDownload(){
            this.loader = 4;
            let date = new Date();
            this.filename = 'prospects-export-'+date.getMonth()+'-'+date.getDate()+'-'+date.getFullYear()+'-'+date.getHours()+'-'+date.getMinutes()+'-'+date.getSeconds()+'.csv';
            this.json_data = [];
            for(var i = 0; i < this.recordContainer.length; i++){
                let record_id = '';
                if(this.allProspects[this.recordContainer[i]].record_id){
                    record_id = this.allProspects[this.recordContainer[i]].record_id;
                }
                let mobilePhones = '';
                if(this.allProspects[this.recordContainer[i]].mobilePhones){
                    mobilePhones = this.allProspects[this.recordContainer[i]].mobilePhones;                   
                    mobilePhones = mobilePhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    mobilePhones = mobilePhones.replace("-", '')
                    mobilePhones = mobilePhones.substring(0,10)
                    if(mobilePhones.length == 10){
                        mobilePhones = parseInt(mobilePhones)
                    }else{
                        mobilePhones = 0;
                    }
                }
                let workPhones = '';
                if(this.allProspects[this.recordContainer[i]].workPhones){
                    workPhones = this.allProspects[this.recordContainer[i]].workPhones;                   
                    workPhones = workPhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    workPhones = workPhones.replace("-", '')
                    workPhones = workPhones.substring(0,10)
                    if(workPhones.length == 10){
                        workPhones = parseInt(workPhones)
                    }else{
                        workPhones = 0;
                    }
                }
                let homePhones = '';
                if(this.allProspects[this.recordContainer[i]].homePhones){
                    homePhones = this.allProspects[this.recordContainer[i]].homePhones;                   
                    homePhones = homePhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    homePhones = homePhones.replace("-", '')
                    homePhones = homePhones.substring(0,10)
                    if(homePhones.length == 10){
                        homePhones = parseInt(homePhones)
                    }else{
                        homePhones = 0;
                    }
                }
                                
                let firstName = '';
                if(this.allProspects[this.recordContainer[i]].first_name){
                    firstName = this.allProspects[this.recordContainer[i]].first_name;
                    firstName = firstName.replace(/[^a-zA-Z0-9\s]/g, "");
                    firstName = firstName.replace("'", '')
                    firstName = firstName.replace("&", '')
                }
                let lastName = '';
                if(this.allProspects[this.recordContainer[i]].last_name){
                    lastName = this.allProspects[this.recordContainer[i]].last_name;
                    lastName = lastName.replace(/[^a-zA-Z0-9\s]/g, "");
                    lastName = lastName.replace("'", '')
                    lastName = lastName.replace("&", '')
                }
                let  addressState = '';
                if(this.allProspects[this.recordContainer[i]].state){
                    addressState = this.allProspects[this.recordContainer[i]].state;
                    addressState = addressState.replace(/[^a-zA-Z0-9\s]/g, "");
                    addressState = addressState.replace("'", '')
                    addressState = addressState.replace("&", '')
                }
                let addressStreet = '';
                if(this.allProspects[this.recordContainer[i]].address){
                    addressStreet = this.allProspects[this.recordContainer[i]].address;
                    addressStreet = addressStreet.replace(/[^a-zA-Z0-9\s]/g, "")
                    addressStreet = addressStreet.replace("'", '')
                    addressStreet = addressStreet.replace("&", '')
                }
                let tags = '';
                if(this.allProspects[this.recordContainer[i]].outreach_tag){                    
                    tags = this.allProspects[this.recordContainer[i]].outreach_tag;
                    tags = tags.replace(/[^a-zA-Z0-9\s]/g, "")
                    tags = tags.replace("'", '')
                    tags = tags.replace("&", '')
                }
                let addressZip = '';
                if(this.allProspects[this.recordContainer[i]].zip){
                    addressZip = this.allProspects[this.recordContainer[i]].zip;
                    addressZip = addressZip.replace(/[^a-zA-Z0-9\s]/g, "")
                    addressZip = addressZip.replace("'", '')
                    addressZip = addressZip.replace("&", '')
                }
                let addressCity = '';
                if(this.allProspects[this.recordContainer[i]].city){
                    addressCity = this.allProspects[this.recordContainer[i]].city;
                    addressCity = addressCity.replace(/[^a-zA-Z0-9\s]/g, "")
                    addressCity = addressCity.replace("'", '')
                    addressCity = addressCity.replace("&", '')
                }
                let company = '';
                if(this.allProspects[this.recordContainer[i]].company){
                    company = this.allProspects[this.recordContainer[i]].company;
                    //company = company.replace(/[^a-zA-Z0-9\s]/g, "")
                    company = company.replace("'", '')
                    company = company.replace("&", '')
                }
                
                let emails = null;
                if(this.allProspects[this.recordContainer[i]].emails){
                    emails = this.allProspects[this.recordContainer[i]].emails
                    if(emails.search(',') > 0){                         
                        emails = emails.substring(0,emails.search(','));
                    } else {
                        emails = this.allProspects[this.recordContainer[i]].emails
                    }
                }
                let stage = '';
                if(this.allProspects[this.recordContainer[i]].stage_data){
                    stage = this.allProspects[this.recordContainer[i]].stage_data.stage;
                    //stage = stage.replace(/[^a-zA-Z0-9\s]/g, "")
                    stage = stage.replace("'", '')
                    stage = stage.replace("&", '')
                }
                let personal_note = '';
                if(this.allProspects[this.recordContainer[i]].personal_note){
                    personal_note = this.allProspects[this.recordContainer[i]].personal_note;
                    personal_note = personal_note.replace(/\r?\n|\r/g, "")
                    personal_note = personal_note.replace("'", '')
                    personal_note = personal_note.replace("&", '')
                }
                let dial_attempts = '';
                if(this.allProspects[this.recordContainer[i]].dial_attempts){
                    dial_attempts = this.allProspects[this.recordContainer[i]].dial_attempts;
                }
                let last_agent = '';
                if(this.allProspects[this.recordContainer[i]].last_agent){
                    last_agent = this.allProspects[this.recordContainer[i]].last_agent;
                }
                let last_agent_dispo_time = '';
                if(this.allProspects[this.recordContainer[i]].last_agent_dispo_time){
                    last_agent_dispo_time = this.allProspects[this.recordContainer[i]].last_agent_dispo_time;
                }
                let last_dispo = '';
                if(this.allProspects[this.recordContainer[i]].last_dispo){
                    last_dispo = this.allProspects[this.recordContainer[i]].last_dispo;
                    last_dispo = last_dispo.replace(/[^a-zA-Z0-9\s]/g, "")
                    last_dispo = last_dispo.replace("'", '')
                    last_dispo = last_dispo.replace("&", '')
                }
                let outreach_tag = '';
                if(this.allProspects[this.recordContainer[i]].outreach_tag){
                    outreach_tag = this.allProspects[this.recordContainer[i]].outreach_tag;
                    outreach_tag = outreach_tag.replace("'", '')
                    outreach_tag = outreach_tag.replace(/[#,]/g, ' ')
                    outreach_tag = outreach_tag.replace("&", '')
                }
                this.json_data[i] = {
                    "record_id" : record_id,
                    "first_name" : firstName,
                    "last_name" : lastName,
                    "mobilePhones" : mobilePhones,
                    "homePhones" : homePhones,
                    "workPhones" : workPhones,
                    "state" : addressState,
                    "stage" : stage,
                    "address": addressStreet,
                    "tags" : tags,
                    "zip" : addressZip,
                    "city" : addressCity,
                    "company" : company,
                    "emails" : emails,
                    "personal_note" : personal_note,
                    "dial_attempts" : dial_attempts,
                    "last_agent" : last_agent,
                    "outreach_tag" : outreach_tag,
                    "last_agent_dispo_time" : last_agent_dispo_time,
                    "last_dispo" : last_dispo,
                }
            }
        },
        finishdownload(){
            this.loader = false;
        },       
        outreachallupdate(){
            axios.get('/api/outreachallupdate').then((response) => {
            //this.stageDetails = response.data;
            });
        },
        getallview(){
            axios.get('/api/get-all-views').then((response) => {
                this.views = response.data.results;
            });
        },
        refreshAll() {
            this.getOutreachData(1);
        },
        deleteGroupRecords(n1, n2, n3, p1) {
            let arr = this.sform.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if (arr[i].number1 === n1 && arr[i].number2 === n2 && arr[i].number3 === n3 && arr[i].first_name === p1) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        deleteRecord(cid) {

            let arr = this.sform.fdata;
            console.log(arr)
            for( var i = 0; i < arr.length; i++){ 
                if ( i === cid) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        deleteUnRecord(cid) {
            arr = this.sform.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if ( arr[i].number1 === cid) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        setFileFields() {
            let fdt = []; let fnw = [];
            let $this = this;
            $this.sform.fdata = [];
            $this.sform.fdata =  $this.parse_csv;
            $this.sform.fdata.forEach(function (key, index) {
                fnw = [];
                $this.parse_header.forEach(function(element, ekey) {
                    //console.log($this.sform.fields.indexOf(element)+' -- '+element);
                    if($this.sform.fields.indexOf(element) >= 0) {
                        let nkey = $this.sform.efields[$this.sform.fields.indexOf(element)];
                        let ov = '';
                        if(nkey == 'number1' || nkey == 'number2' || nkey == 'number3') {
                            ov = $this.sformatNumber($this.sform.fdata[index][element]);
                        } else {
                            ov = $this.sform.fdata[index][element];
                        }
                        $this.sform.fdata[index][nkey] = ov;
                        delete $this.sform.fdata[index][element];
                    }
                    else {
                      delete $this.sform.fdata[index][element];
                    }
                    /* let ov = ''; let val = '';
                    if($this.sform.fields[ekey] != '') {
                        val = (key[$this.sform.fields[ekey]])?key[$this.sform.fields[ekey]]:'';
                        if(element == 'number1' || element == 'number2' || element == 'number3') {
                            ov = $this.sformatNumber(val);
                        } else {
                            ov = val;
                        }
                        fnw[element] = ov;
                    } */
                });
                
            });
           // console.log($this.sform.fdata);
            this.makingGroupedRecords();
            this.loader = false;
        },
        startSyncing() {
            if(this.sform.destination == 'five9' && this.sform.name == '' && this.sform.lid == '0') {
                Vue.$toast.warning("List name is mandatory !!");
                return false;
            }
            this.loader = 4;
            this.sform.post('/api/uploadContactsDashboard').then((response) => {
                this.loader = false;
                //this.form.reset();
                //this.step = 4;
                //this.report = response.data.result.message;
                if(response.data.status == 'success'){
                    this.sform.reset();
                    this.step = 3;
                    this.report = response.data.message; //"Inserted: "+ response.data.listRecordsInserted+", Updated: "+response.data.crmRecordsUpdated;
                } else if(response.data.status == 'error'){
                    this.errornote = response.data.message; //"Inserted: "+ response.data.listRecordsInserted+", Updated: "+response.data.crmRecordsUpdated;
                } else {
                    Vue.$toast.warning(response.data.result);
                }
            })
        },
        checkRecords(){
            if(this.sform.fdata.length > this.unique_Records) {
                let diff = this.sform.fdata.length - this.unique_Records;
                Vue.$toast.info(diff +' records are still duplicate. We will convert them to '+ this.duplicate_Records +' unique records.', { duration: 8000 });
            }
            this.step = 2;            
        },
        swapRecords(n1, n2, n3){
            if(n2 == 0) {
                Vue.$toast.error("Numbers can not be swapped. Number1 can not be 0 !!");
                return false;
            }
            if(n1 == n2) {
                Vue.$toast.error("Numbers can not be swapped. Both numbers are same !!");
                return false;
            }
            let arr = this.sform.fdata; let intrchnage = '';
            for( var i = 0; i < arr.length; i++){ 
                if (arr[i].number1 === n1 && arr[i].number2 === n2 && arr[i].number3 === n3) { 
                    arr[i].number1 = n2;
                    arr[i].number2 = n1;
                }
            }
            this.makingGroupedRecords();

            Vue.$toast.info("Numbers swapped successfully !!");
        }, 
    },
    beforeMount() {
        // axios.get('/api/get-f9-list').then((response) => {
        //     this.five9_list = response.data;
        // });
        // axios.get('/api/get-f9-campaigns').then((response) => {
        //     this.five9_campaigns = response.data;
        // });
    },
    mounted() {
        this.getOutreachData(1);
        var path = this.$route.path.split("/")
        var id = path[path.length-1]
        this.accountId = id
        axios.get('/api/get-account-info/' + path[path.length - 1]).then((response) => {
            this.accountInfo = response.data.results
        })
        axios.get('/api/get-all-stages-account/' + path[path.length-1]).then((response) => {
            this.stageDetails = response.data;
        });
        axios.get('/api/get-mapping-range').then((response) => {
            this.mapped_ranges = response.data;
        });
        this.getallview()
        axios.get('/api/get-all-filter').then((response) => {
            this.filterItems = response.data.items
            this.filterItemsAll = response.data.items
        });
    }
}
</script>
<style>
    .pointer-hand{
        cursor: pointer;
    }
    .border-none{
        border:none !important;
        margin-right:3px;
    }
</style>