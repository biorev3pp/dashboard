<template>
    <div>
        <div v-if="step == 0">
            <div class="filterbox">
                <div class="row mx-0 mb-1">
                    <div class="col-md-7 col-12 pl-0">
                        <div class="filter-btns-holder">
                            <span class="filter-btns mb-0" v-if="form.sdate" :key="'fk'+form.sdate">
                                <span  class="text-dark mx-1 pointer-hand" v-title="'Date range is from '+form.sdate+' to '+form.edate"> 
                                    Date is {{ form.sdate | setdate }} - {{ form.edate | setdate }}
                                </span>
                            </span>
                            <span v-if="form.list" class="filter-btns mb-0" :key="'fk'+form.list">
                                <span  class="text-dark mx-1 pointer-hand" v-title="'List is '+form.list"> 
                                    List is {{ form.list }}
                                </span>
                            </span>
                            <span v-if="form.number_type" class="filter-btns mb-0" :key="'fk'+form.number_type">
                                <span  class="text-dark mx-1 pointer-hand" v-title="'Number Filter is '+form.number_type"> 
                                    Number Filter is {{ form.number_type }}
                                </span>
                            </span>
                            <span v-if="form.agentName" class="filter-btns mb-0" :key="'fk'+form.agentName">
                                <span  class="text-dark mx-1 pointer-hand" v-title="'Agent is '+form.agentName"> 
                                    Agent is {{ form.agentName }}
                                </span>
                            </span>
                            <span class="filter-btns row mb-0" v-if="form.disposition" :key="'fk'+form.disposition">
                                <span v-title="'Disposition is '+form.disposition.join(',')"  class="text-dark mx-1 pointer-hand">
                                    Disposition is {{ form.disposition.join(',') }}
                                </span>
                            </span>
                            <span class="filter-btns row mb-0" v-if="form.talk_time" :key="'fk'+form.talk_time">
                                <span v-title="'Talk Time is '+((form.talk_time == 5)?'0-5':(form.talk_time == 10)?'6-10':(form.talk_time == 20)?'11-20':'20+')+' sec'"  class="text-dark mx-1 pointer-hand">
                                    Talk Time is {{ (form.talk_time == 5)?'0-5':(form.talk_time == 10)?'6-10':(form.talk_time == 20)?'11-20':'20+' }} sec
                                </span>
                            </span>
                            <span class="filter-btns row mb-0" v-if="form.acw" :key="'fk'+form.acw">
                                <span v-title="'ACW is '+((form.acw == 1)?'0-1':(form.acw == 2)?'1-2':(form.acw == 3)?'2-3':'3+')+' mins'"  class="text-dark mx-1 pointer-hand">
                                    ACW is {{ (form.acw == 1)?'0-1':(form.acw == 2)?'1-2':(form.acw == 3)?'2-3':'3+' }} mins
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2 col-12 p-0 text-right">
                        <img :src="loader_url" alt="Loading..." v-show="loader">
                        <span class="float-right"  v-if="recordContainer.length >= 1">
                            <span class="float-left d-inline-block mr-2 text-center">
                                <b>{{ recordContainer.length | freeNumber }}</b> Records<br>
                                <a class="cursor-pointer" style="position:relative;top:-5px" @click="selectAllRecords()"><u>Select All</u></a>
                            </span>
                            <div class="dropdown d-inline-block">
                                <a class="btn btn-outline-danger btn-sm theme-btn dropdown-toggle" href="#" role="button" id="dropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> EXPORT </a>
                                <div class="dropdown-menu source-dropdown left--65" aria-labelledby="dropdownMenuLink2">
                                    <a href="javascript:;" @click="StartExport" class="dropdown-item text-uppercase"> Five9</a>
                                </div>
                            </div>
                        </span>
                    </div>
                    <div class="col-md-3 col-12 pr-0 text-right form-inline d-block">
                        <span class="mr-1">
                            <label class="form-control  pr-0  border-none"> Sort By : </label>
                            <select class="form-control" v-model="form.sortType" @change="getDatasetsCall(1)">
                                <option value="outreach_touched_at">Last Contacted</option>
                                <option value="last_outreach_engage">Last Engaged</option>
                                <option value="last_update_at">Last Updated</option>
                                <option value="first_name">First Name</option>
                            </select>
                        </span>
                        <span class="">
                            <i class="bi bi-arrow-down active pointer-hand" v-if="form.sortBy == 'desc'" @click="updateSorting('asc')"></i>
                            <i class="bi bi-arrow-up active pointer-hand" v-else  @click="updateSorting('desc')"></i>
                        </span>
                        <span class="ml-4">
                            <button class="btn btn-sm btn-default" @click="refreshAll"> 
                                <i class="bi bi-bootstrap-reboot"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="row mx-0 mb-1">
                    <div class="col-md-9 col-12 p-0 filter-btns-holder">
                        <span v-for="(filter,index) in form.filterConditionsArray" :key="'filter-'+filter.conditionText+'-'+filter.formula+'-'+filter.textCondition" class="filter-btns row" v-show="filter_expand" :class="'filterbtn-'+index">
                            <span v-title="filter.textConditionLabel"  class="text-dark mx-1 pointer-hand" @click="showFilterDetails(filter, index)"> {{ filter.textConditionLabel }}</span>
                            <i class="bi bi-x pr-1  pointer-hand" @click="removeFilter(index)"></i>
                        </span>
                        <span class="filtertemp">
                            <span class="filter-btns row" v-show="(filter == true) && (typeof form.filter == 'object')" >
                                <span  class="text-dark mx-1 pointer-hand"> {{ (form.filter)?form.filter.filter:'-' }}</span>
                                <i class="bi bi-x pr-1  pointer-hand"></i>
                            </span>
                        </span>
                        <div class="stage-select-box selection-box filteration-box" v-show="filter" :style="'left:'+leftpos+';top:'+toppos">
                            <div class="stage-box-header">
                                <i class="bi bi-x close" @click="filter = false; form.filter=''"></i>
                                <span class="control-label text-uppercase">Select Options</span>
                            </div>
                            <div class="stage-box-body p-2">
                                <div class="row" v-show="filterEmail">
                                    <div class="col-md-12 pr-1 radlabl">
                                        <input type="radio" name="outreach-email" v-model="queryType" value="all" class="btn-check" :id="form.filter.filter_key +'-email-all'" autocomplete="off">
                                        <label class="btn" :for="form.filter.filter_key +'-email-all'">All</label>
                                        <input type="radio" name="outreach-email" v-model="queryType" value="any" class="btn-check" :id="form.filter.filter_key +'-email-any'" autocomplete="off">
                                        <label class="btn" :for="form.filter.filter_key +'-email-any'">Any</label>
                                        <input type="radio" name="outreach-email" v-model="queryType" value="none" class="btn-check" :id="form.filter.filter_key +'-email-none'" autocomplete="off">
                                        <label class="btn" :for="form.filter.filter_key +'-email-none'">None</label>
                                        <br>
                                        <span v-for="filterOption in filterOptions" :key="'select-'+filterOption" :value="filterOption" class="mselect-bs" v-show="queryType != 'none'">
                                            <input type="checkbox" v-model="searchfilterEmailMoreOption" :id="form.filter.filter_key +'-'+filterOption" :value="filterOption"/>
                                            <label class="" :for="form.filter.filter_key +'-'+filterOption">{{ filterOption }}</label><br>
                                        </span>
                                    </div>
                                </div>
                                <div class="row" v-show="filterPhone">
                                    <div class="col-md-12 pr-1 radlabl">
                                        <input type="radio" name="outreach-mobile" v-model="queryType" value="all" class="btn-check" :id="form.filter.filter_key +'-phone-all'" autocomplete="off">
                                        <label class="btn" :for="form.filter.filter_key +'-phone-all'">All</label>
                                        <input type="radio" name="outreach-mobile" v-model="queryType" value="any" class="btn-check" :id="form.filter.filter_key +'-phone-any'" autocomplete="off">
                                        <label class="btn" :for="form.filter.filter_key +'-phone-any'">Any</label>
                                        <input type="radio" name="outreach-mobile" v-model="queryType" value="none" class="btn-check" :id="form.filter.filter_key +'-phone-none'" autocomplete="off">
                                        <label class="btn" :for="form.filter.filter_key +'-phone-none'">None</label>
                                        <br>
                                        <span v-for="filterOption in filterOptions" :key="'select-'+filterOption" :value="filterOption" class="mselect-bs" v-show="queryType != 'none'">
                                            <input type="checkbox" v-model="searchfilterPhoneMoreOption" :id="form.filter.filter_key +'--'+filterOption" :value="filterOption"/>
                                            <label class="" :for="form.filter.filter_key +'--'+filterOption">{{ filterOption }}</label><br>
                                        </span>
                                    </div>
                                </div>
                                <div v-show="!filterEmail && !filterPhone">
                                    <div class="wf-150 pr-1">
                                        <select  class="form-control mb-2 fs-14 bt-bottom-border" v-model="form.filterOption" v-if="(filterInput || filterDropdown) && (filter != null)" @change="setInputTestOrDropdown">
                                            <option v-for="filterOption in filterOptions" :key="'select-'+filterOption" :value="filterOption">{{ filterOption }}</option>
                                        </select>
                                    </div>
                                    <div class="pl-1" v-show="showInputTextOrDropdown">
                                        <div v-show="filterInput">
                                            <input type="text" class="form-control" v-model="form.filterText" placeholder="">
                                        </div>
                                        <div v-show="filterDropdown">
                                            <div class="selectedOptions">
                                                <span v-for="(option, index) in selectedOptions" :key="'option-'+index" @click="removeSelectedOption(index)">{{ option }} </span> 
                                            </div>
                                            <select class="form-control" v-model="form.dropdown" @change="getSelectedOptions">
                                                <option v-for="select in selects" :key="'select-'+select.oid" :value="select.oid">{{ select.name }}</option>
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
                            </div>
                            <div class="stage-box-footer text-center border-top p-2">
                                <button v-show="filterBtn" v-if="filterInput || filterDropdown || filterDateRange || filterEmail || filterPhone" class="btn btn-primary btn-sm" @click="createFilter">Done</button>
                                <button v-show="!filterBtn" v-if="filterInput || filterDropdown || filterDateRange || filterEmail || filterPhone" class="btn btn-primary btn-sm" @click="updateFilter">Update</button>
                            </div> 
                        </div>
                        <div class="position-relative d-inline-block">
                            <button type="button" class="btn btn-outline-dark btn-sm text-capitalize" @click="showFilter()"><i class="bi bi-plus"></i> Add filter</button>
                            <div class="stage-select-box selection-box" v-show="showView">
                                <div class="stage-box-header">
                                    <i class="bi bi-x close" @click="showView = false"></i>
                                    <span class="control-label text-uppercase">Select Filters</span>
                                </div>
                                <div class="stage-box-body">
                                    <p class="search-filter">
                                        <input type="text" placeholder="Search Your Field" v-model="filter_keyword">
                                    </p>
                                    <div class="fh-250">
                                        <a href="javascript:;" class="stage-link" v-for="(fitem, fk) in filterScanItems" :key="'fkey-'+fk" @click="showFilterOption(fitem)">{{ fitem.filter }}</a>
                                    </div>
                                </div> 
                            </div>
                            <span class="text-secondary cursor-pointer ml-1" v-if="filter_expand" @click="filter_expand = false">Hide Filters</span>
                            <span class="text-secondary cursor-pointer ml-1" v-else @click="filter_expand = true">{{ form.filterConditionsArray.length }} Hidden Filters</span>
                            <span class="text-danger cursor-pointer ml-1" v-show="form.filterConditionsArray.length >= 1" @click="removeAllFilter">Clear All</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-12 p-0 text-right pt-2">
                        <img :src="loader_url" alt="Loading..." v-show="loader">
                        <span v-if="recordContainer.length >= 1"> Selected  <b>{{ recordContainer.length | freeNumber }}</b> of  </span>
                        <span v-else>Showing</span>
                        <span><b>{{ totalNumberOfRecords | freeNumber }}</b> Results</span>
                    </div>
                </div>
            </div>
            <div class="divtable border-top" v-show="!showProgress">
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-45 text-center">
                            <input type="checkbox" name="" id="check-all" value="0" aria-label="..." @click="addAndRemoveAllRecordToContainer">
                        </div>
                        <div class="divthead-elem mwf-200">Name</div>
                        <div class="divthead-elem wf-200">Tag</div>
                        <div class="divthead-elem wf-200">Stage</div>
                        <div class="divthead-elem wf-150">Call Stack</div>
                        <div class="divthead-elem wf-220">Email Stack</div>
                        <div class="divthead-elem wf-150">Time Stack</div>
                    </div>
                </div>
                <div class="divtbody custom-height-220">
                    <div class="divtbody-row" v-for="record in records.data" :key="'dsg-'+record.id">
                        <div class="divtbody-elem  wf-45 text-center">
                            <input :id="'record-'+record.id" class="form-check-input m-0" type="checkbox" :value="record.id" @click="addAndRemoveRecordToContainer(record.id)">
                        </div>
                        <div class="divtbody-elem mwf-200">
                            <router-link target="_blank" :to="'/prospects/' + record.record_id" class="text-capitalize"><b>{{ record.first_name }} {{ record.last_name }} </b></router-link>
                            <br>
                            <small class="fw-500" v-title="record.title" v-if="record.title">{{ record.title }}  in </small> 
                            <span class="company-sm" v-title="record.company" v-if="record.company">{{ record.company }}</span>
                        </div>
                        <div class="divtbody-elem wf-200">
                            <span v-if="record.outreach_tag">
                                <label class=" alert alert-primary m-1 py-1 px-2" v-for="(tag, ti) in (record.outreach_tag.split(','))" v-title="tag" :key="'otg'+ti">{{ tag }}</label>
                            </span>    
                        </div>
                        <div class="divtbody-elem wf-200">
                            <span v-if="record.stage_name" :class="record.stage_css" v-title="record.stage_name">
                                {{ record.stage_name }}
                            </span>
                            <span class="stage-och stage-1" v-else>No Stage</span>
                        </div>
                        <div class="divtbody-elem wf-150">
                            <span class="stack-box call-log cursor-pointer" @click="showDispo(record.record_id)" v-if="called">
                                <label for="call">
                                    <i class="bi bi-telephone-fill"></i>
                                </label>
                                <call-log :called_numbers="called_numbers" :call="record.mcall_attempts" :rcall="record.mcall_received" :title="record.mobilePhones" :label="'MP'" :rid="record.record_id"></call-log>
                                <call-log :called_numbers="called_numbers" :call="record.wcall_attempts" :rcall="record.wcall_received" :title="record.workPhones" :label="'WP'" :rid="record.record_id"></call-log>
                                <call-log :called_numbers="called_numbers" :call="record.hcall_attempts" :rcall="record.hcall_received" :title="record.homePhones" :label="'HP'" :rid="record.record_id"></call-log>
                            </span>
                        </div>
                        <div class="divtbody-elem wf-220">
                            <span class="stack-box email-log cursor-pointer" @click="showProspect(record.record_id)">
                                <label for="email">
                                    <i class="bi bi-envelope-fill text-primary"></i>
                                </label>
                                <email-log :te="record.email_delivered" :tc="record.email_clicked" :to="record.email_opened" :tb="record.email_bounced" :tr="record.email_replied" :title="record.emails" :label="'B'"></email-log>
                                <email-log :te="0" :tc="0" :to="0" :tb="0" :tr="0" :title="record.supplemental_email?record.supplemental_email:''" :label="'S'"></email-log>
                            </span>
                        </div>
                        <div class="divtbody-elem  wf-150">
                            <span class="stack-box stack-time-box">
                                <label for="email">
                                    <i class="bi bi-clock-fill text-success"></i>
                                </label>
                                <span :class="(record.outreach_created_at)?'active':''" v-title="myDateFormat('Created', record.outreach_created_at)">C</span>
                                <span :class="(record.outreach_touched_at)?'active':''" v-title="myDateFormat('Touched', record.outreach_touched_at)">T</span>
                                <span :class="(record.last_update_at)?'active':''" v-title="myDateFormat('Updated', record.last_update_at)">U</span>
                                <span :class="(record.last_agent_dispo_time)?'active':''" v-title="myDateFormat('Last Called ', record.last_agent_dispo_time)">CT</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="divtfoot border-top">
                    <div class="text-center py-1">
                        <span class="form-inline d-inline-flex mr-3">
                            <label class="form-control  pr-0 border-none"> Show : </label>
                            <select class="form-control" v-model="form.recordPerPage" @change="getDatasetsCall">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </span>
                        <pagination :limit="5" :data="records" @pagination-change-page="getDatasetsCall"></pagination>
                    </div>
                </div>
            </div>
            <div class="center" v-show="showProgress">
                <radial-progress-bar 
                    :startColor="startColor"
                    :innerStrokeWidth="5"
                    :stopColor="stopColor"
                    :diameter="200"
                    :completed-steps="completedSteps"
                    :total-steps="totalSteps">
                    <h1>{{ completedSteps }}/{{ totalSteps }}</h1>
                    <!-- <button type="button" class="btn btn-primary btn-sm" @click="completedSteps = completedSteps + 1">Next</button> -->
                </radial-progress-bar>
            </div>
        </div>
        <div v-else-if="step >= 1">
            <call-export-wizard :ntype="form.number_type" :step="step" :data="fivenineData" v-if="form.number_type" />
            <call-wizard :step="step" :data="fivenineData" v-else />
        </div>
        <div v-else>
        </div>
        <div class="modal fade" id="dispoModal" tabindex="-1" role="dialog" aria-labelledby="dispoModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-uppercase" id="dispoModalLabel">Disposition Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" v-if="loader == true">
                        <p class="text-center text-danger m-4">
                            <img :src="loader_url" alt="Loading...">
                        </p>
                    </div>
                    <div class="modal-body" v-else-if="dispo_data.length >= 1">
                        <div class="row m-0">
                            <div class="col-md-4 p-0 border" v-for="(ddata, dkey) in dispo_data" :key="'dd'+dkey">
                                <h5 class="dispo-title" :class="[(ddata.number_type == '' || ddata.number_type == '0')?'alert-danger '+calledGreen(ddata.number):''+calledGreen(ddata.number)]">
                                    <span v-if="ddata.number_type == 'm'">Mobile Phone</span>
                                    <span v-else-if="ddata.number_type == 'd'">Work Phone</span>
                                    <span v-else-if="ddata.number_type == 'hq'">Home Phone</span>
                                    <span v-else>Other</span><br>
                                    {{ ddata.number | formatPhoneNumber }}
                                </h5>
                                <div>
                                    <ul class="dispo-list">
                                        <li class="text-uppercase" v-for="(dddispo, ddd) in ddata.dispositions" :key="'ddi'+ddd">
                                            {{ dddispo }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-else>
                        <p class="text-center text-danger m-4">
                            No Data Found
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="fullsidebar2" id="prospectModal">
            <div class="sidebar-content">
                <div class="sidebar-header">
                    <h5 class="sidebar-title">Email Details For Prospect - {{ prospect }}</h5>
                    <button type="button" class="close" @click="closeSideBar()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="sidebar-body">
                    
                    <p class="text-center p-4" v-if="loading">
                        <img :src="loader_url" alt="loading...">
                    </p>
                    <div v-else>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" :class="[(emailShow == 1)?'active':'']" aria-current="page" href="#" @click="emailShow = 1">All</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="[(emailShow == 2)?'active':'']" aria-current="page" href="#" @click="emailShow = 2">Single</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="[(emailShow == 3)?'active':'']" aria-current="page" href="#" @click="emailShow = 3">Sequence</a>
                            </li>
                            <!-- 
                            <li class="nav-item">
                                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
                            </li> -->
                        </ul>
                    <br>
                        <div>
                            <table class="table table-striped table-bordered mb-2 table-condensed" v-show="emailShow == 1">
                                <tbody>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Sender</th>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th>Delivered At</th>
                                        <th>Opene At</th>
                                        <th>Clicked At</th>
                                        <th>Replied At</th>
                                        <th>Bounced At</th>
                                    </tr>
                                    <tr v-for="(mail, mk) in allemails" :key="'mk'+mk">
                                        <td>{{ mk + 1 }}</td>
                                        <td>{{ mail.mailboxAddress }}</td>
                                        <td>{{ mail.subject }}</td>
                                        <td>{{ mail.mailingType }}</td>
                                        <td>
                                            <span v-if="mail.deliveredAt">{{ mail.deliveredAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>
                                        <td>
                                            <span v-if="mail.openedAt" >{{ mail.openedAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>
                                        <td>
                                            <span v-if="mail.clickedAt">{{ mail.clickedAt | setFulldate }}</span>
                                            <sapn v-else>---</sapn>
                                        </td>
                                        <td>
                                            <span v-if="mail.repliedAt">{{ mail.repliedAt | setFulldate }}</span>
                                            <span v-else></span>
                                        </td>                                        
                                        <td>
                                            <span v-if="mail.bouncedAt">{{ mail.bouncedAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>  
                            <table class="table table-striped table-bordered mb-2 table-condensed" v-show="emailShow == 2">
                                <tbody>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Sender</th>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th>Delivered At</th>
                                        <th>Opene At</th>
                                        <th>Clicked At</th>
                                        <th>Replied At</th>
                                        <th>Bounced At</th>
                                    </tr>
                                    <tr v-for="(mail, mk) in singleemails" :key="'mk'+mk">
                                        <td>{{ mk + 1 }}</td>
                                        <td>{{ mail.mailboxAddress }}</td>
                                        <td>{{ mail.subject }}</td>
                                        <td>{{ mail.mailingType }}</td>
                                        <td>
                                            <span v-if="mail.deliveredAt">{{ mail.deliveredAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>
                                        <td>
                                            <span v-if="mail.openedAt" >{{ mail.openedAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>
                                        <td>
                                            <span v-if="mail.clickedAt">{{ mail.clickedAt | setFulldate }}</span>
                                            <sapn v-else>---</sapn>
                                        </td>
                                        <td>
                                            <span v-if="mail.repliedAt">{{ mail.repliedAt | setFulldate }}</span>
                                            <span v-else></span>
                                        </td>                                        
                                        <td>
                                            <span v-if="mail.bouncedAt">{{ mail.bouncedAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>  
                            <table class="table table-striped table-bordered mb-2 table-condensed" v-show="emailShow == 3">
                                <tbody>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Sender</th>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th>Delivered At</th>
                                        <th>Opene At</th>
                                        <th>Clicked At</th>
                                        <th>Replied At</th>
                                        <th>Bounced At</th>
                                    </tr>
                                    <tr v-for="(mail, mk) in sequenceemails" :key="'mk'+mk">
                                        <td>{{ mk + 1 }}</td>
                                        <td>{{ mail.mailboxAddress }}</td>
                                        <td>{{ mail.subject }}</td>
                                        <td>{{ mail.mailingType }}</td>
                                        <td>
                                            <span v-if="mail.deliveredAt">{{ mail.deliveredAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>
                                        <td>
                                            <span v-if="mail.openedAt" >{{ mail.openedAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>
                                        <td>
                                            <span v-if="mail.clickedAt">{{ mail.clickedAt | setFulldate }}</span>
                                            <sapn v-else>---</sapn>
                                        </td>
                                        <td>
                                            <span v-if="mail.repliedAt">{{ mail.repliedAt | setFulldate }}</span>
                                            <span v-else></span>
                                        </td>
                                        <td>
                                            <span v-if="mail.bouncedAt">{{ mail.bouncedAt | setFulldate }}</span>
                                            <span v-else>---</span>
                                        </td>                                        
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import CallLog from './CallLog';
import EmailLog from './EmailLog';
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import { ToggleButton } from 'vue-js-toggle-button';
import 'vue-select/dist/vue-select.css';
import CallExportWizard from '../dataset/CallFiveNineWizard';
import CallWizard from '../dataset/CallWizard';
import RadialProgressBar from 'vue-radial-progress';
export default {
    components:{CallLog, EmailLog, DateRangePicker, ToggleButton, CallExportWizard, RadialProgressBar, CallWizard},
    data() {
        return {
            callDiff : 100,
            showProgress : false,
            startColor: "#3490dc",
            stopColor: "#f8f9fa",
            completedSteps: 0,
            totalSteps: 0,
            fivenineData : [],
            recordContainer:[],
            dispo_data:[],
            bypassFIlterKey : '',
            filter_expand:true,
            loader:false,
            step:0,
            records:{},
            totalRecords : 0,
            active_row:'',
            searchfilterEmailMoreOption:[],
            searchfilterPhoneMoreOption:[],
            queryType : "",
            leftpos:'0px',
            toppos:'0px',
            exportForm : new Form({
                exports : []
            }),
            emailShow : 1,
            allemails : [],
            singleemails : [],
            sequenceemails : [],
            form: new Form({
                agentName: '',
                type : '',
                sortType:'outreach_touched_at',
                sortBy:'desc',
                recordPerPage:20,
                pageno:1,
                datatset:'',
                dateRange:{},
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
                filterEmailMoreOption : [],
                filterText:'',
                filterConditionsArray : [],
                viewName : '',
                sharing : 'private',
                acw:'',
                acw_plus:'',
                agentName:'',
                disposition:[],
                edate:'',
                sdate:'',
                talk_time:'',
                talk_time_plus:'',
                list:'',
                number_type:''
            }),
            showView : false, //control appearance of view controls
            loader_url: '/img/spinner.gif',
            totalNumberOfRecords:'',
            filter : false,
            filterEmail : false,
            filterPhone : false,
            filterInput : false,
            filterDropdown : false,
            filterDateRange : false,
            filterOptions : [],
            selects : [],
            filterConditionsObject : {},            
            filterConditionsArrayOld : [],
            filterBtn : true,
            filterUpdateBtn: false,
            selectedOptions : [],
            selectedOptionsId : [],
            filterItemsIds : [],
            showInputTextOrDropdown : true,
            filter_keyword:'',
            prospect:{},
            loading: false,
            called_numbers:[],
            called: false,
        }
    },
    filters: {
        formatPhoneNumber(phoneNumberString) {
            var cleaned = ('' + phoneNumberString).replace(/\D/g, '');
            var match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
            if (match) {
                return '(' + match[1] + ') ' + match[2] + '-' + match[3];
            }
            return null;
        }
    },
    computed: {
        stageDetails() {
            return this.$store.getters.stages
        },
        filterItems() {
            return this.$store.getters.datasetFilter
        },
        filterScanItems() {
            if(this.filter_keyword == '') {
                return this.filterItems;
            }
            else {
                return this.filterItems.filter(item => {
                    return item.filter.toLowerCase().indexOf(this.filter_keyword.toLowerCase()) > -1
                })
            }
        },
    },
    methods: {
        calledGreen(str) {
            let nmbr;
            str = str.replace(/([-() ])+/g, '');
            if(str != null && str != 0 && str != 'undefined' && str != '') {
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
                    nmbr = 0;
                } else {
                    nmbr = parseInt(str);
                }
            } else {
                nmbr = 0;
            }
            if(this.called_numbers.indexOf(nmbr) >= 0) {
                return 'bg-success text-white'
            } else {
                return '';
            }
        },
        addAndRemoveAllRecordToContainer(){
            var a = document.getElementById('check-all');
            var aa = document.querySelectorAll("input[type=checkbox]");
            if(document.getElementById("check-all").checked == true){ 
                for (var i = 0; i < aa.length; i++){
                    if(parseInt(aa[i].value) > 0){
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
                document.getElementById("record-"+id).checked = true
            }else{
                this.recordContainer.splice(this.recordContainer.indexOf(parseInt(id)), 1);
                document.getElementById("record-"+id).checked = false
            }
        },
        removeAllFilter() {
            this.form.filterConditionsArray = [];
            this.filterBtn = true
            this.showView = false
            this.filter = false
            this.getDatasetsCall(1);
        },
        setInputTestOrDropdown(){
            //showInputTextOrDropdown
            if(this.form.filterOption == 'is empty' || this.form.filterOption == 'is not empty'){
                this.showInputTextOrDropdown = false
            } else {
                this.showInputTextOrDropdown = true
            }
        },
        showFilter(){
            // this.searchfilterEmailMoreOption = []
            // this.searchfilterPhoneMoreOption = []
            this.filterBtn = true
            this.filterInput = false
            this.filterDropdown = false
            this.filterDateRange = false
            this.filterEmail = false
            this.filterPhone = false
            this.showView = true
            this.filter = false
            this.filter_keyword = ''
        },
        showFilterOption(fitem){
            this.selectedOptions = []
            this.selectedOptionsId = []
            this.filter = true
            this.showView = false
            this.form.filterOption = ''
            this.form.filterText = ''
            if(this.form.filter == null){
                this.filterInput = false;
                this.filterDropdown = false;
                this.filterDateRange = false;
            }else{
                var isConExist = this.form.filterConditionsArray.filter(function(e){
                    return ((e.type == fitem.filter_type) && (e.condition == fitem.filter_key))
                });
                if(isConExist.length > 0){               
                    this.showFilterDetails(isConExist[0], this.form.filterConditionsArray.indexOf(isConExist[0]))
                    return false;
                }
                this.offset('.filtertemp', 5);
                this.form.filter = fitem;
                this.filterOptions = this.form.filter.filter_option.split(',')
                this.form.filterOption = this.filterOptions[0]
                if(this.form.filter.filter_type == 'textbox'){
                    this.filterInput = true;
                    this.filterDropdown = false;
                    this.filterDateRange = false;
                    this.filterEmail = false
                    var oldData = this.form.filterConditionsArray.filter(function(e){
                        return ((e.type == filterType) && (e.condition == filterKey))
                    })
                    if(oldData.length > 0){                        
                        this.showFilterDetails(oldData[0], this.form.filterConditionsArray.indexOf(oldData[0]))
                    }
                }
                if(this.form.filter.filter_type == 'calendar'){
                    this.filterInput = false;
                    this.filterDropdown = false;
                    this.filterDateRange = true;
                    this.filterEmail = false
                }
                if(this.form.filter.filter_type == 'dropdown'){                    
                    var filterType = this.form.filter.filter_type
                    var filterKey = this.form.filter.filter_key
                    var oldData = this.form.filterConditionsArray.filter(function(e){
                        return ((e.type == filterType) && (e.condition == filterKey))
                    })
                    if(oldData.length > 0){                        
                        this.showFilterDetails(oldData[0], this.form.filterConditionsArray.indexOf(oldData[0]))
                    }
                    this.selects = []
                    this.filterInput = false;
                    this.filterDropdown = true;
                    this.filterDateRange = false;
                    this.filterEmail = false
                    let api = this.form.filter.api
                    axios.get(api).then((response) => {
                        this.selects = response.data.results;
                    });
                }
                if(this.form.filter.filter_type == 'email'){
                    this.form.searchfilterEmailMoreOption = []
                    this.filterInput = false
                    this.filterDropdown = false
                    this.filterDateRange = false
                    this.filterEmail = true
                    this.filterPhone = false
                }
                if(this.form.filter.filter_type == 'phone'){
                    this.form.searchfilterPhoneMoreOption = []
                    this.filterInput = false
                    this.filterDropdown = false
                    this.filterDateRange = false
                    this.filterEmail = false
                    this.filterPhone = true
                }
            }
        },
        setfilterEmailMoreOptionAll(data){   
            this.queryType = 'all'           
            this.searchfilterEmailMoreOption = []
            for(var i = 0; i < this.filterOptions.length; i++){
                this.searchfilterEmailMoreOption[i] = this.filterOptions[i]
            }
        },
        setfilterEmailMoreOptionAny(data){   
            this.queryType = 'any'         
            this.searchfilterEmailMoreOption = []
            for(var i = 0; i < this.filterOptions.length; i++){
                this.searchfilterEmailMoreOption[i] = this.filterOptions[i]
            }
        },
        setfilterPhoneMoreOptionAll(data){   
            this.queryType = 'all'           
            this.searchfilterPhoneMoreOption = []
            for(var i = 0; i < this.filterOptions.length; i++){
                this.searchfilterPhoneMoreOption[i] = this.filterOptions[i]
            }
        },
        setfilterPhoneMoreOptionAll(data){   
            this.queryType = 'any'         
            this.searchfilterPhoneMoreOption = []
            for(var i = 0; i < this.filterOptions.length; i++){
                this.searchfilterPhoneMoreOption[i] = this.filterOptions[i]
            }
        },
        removeSelectedOption(index){
            this.selectedOptions.splice(index,1)
            this.selectedOptionsId.splice(index,1)
        },
        getSelectedOptions(){            
            var id = this.form.dropdown
            var data = this.selects.filter(function(e){
                return e.oid == id
            })
            if(this.selectedOptions.indexOf(data[0].name) == -1){
                this.selectedOptions.push(data[0].name)
                this.selectedOptionsId.push(data[0].oid)
            }
        },
        createFilter(){   
            var oldformula = '';       
            if(this.form.filter.filter_type == 'textbox'){
                var textCondition = this.form.filterText  
                if(this.form.filterOption == 'is empty' || this.form.filterOption == 'is not empty'){
                    var textConditionLabel =this.form.filter.filter +' '+ this.form.filterOption
                } else {
                    var textConditionLabel =this.form.filter.filter +' '+ this.form.filterOption +' '+ this.form.filterText
                }
                var api = '';
                oldformula = this.form.filter.filter_option
            }
            
            if(this.form.filter.filter_type == 'calendar'){
                    var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                    var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                    var textCondition = startDate+'--'+endDate
                    var textConditionLabel = this.form.filter.filter +' '+ startDate.substring(0,10)+ ' to ' + endDate.substring(0,10)
                    var api = '';
                    oldformula = this.form.filter.filter_option
            }
            if(this.form.filter.filter_type == 'dropdown'){

                var formdropdown = this.form.dropdown
                var textCondition = this.selectedOptionsId.join(',')
                if(this.form.filterOption == 'is empty' || this.form.filterOption == 'is not empty'){
                    var textConditionLabel = this.form.filter.filter +' '+ this.form.filterOption
                } else {
                    var textConditionLabel = this.form.filter.filter +' '+ this.form.filterOption +' ' + this.selectedOptions.join(', ')
                }
                
                var api = this.form.filter.api
                oldformula = this.form.filter.filter_option
            }
            if(this.form.filter.filter_type == 'email'){
                var textCondition = this.searchfilterEmailMoreOption.join(", ")

                if(this.queryType == 'all'){
                    var query = 'and'
                }else if(this.queryType == 'any'){
                    var query = 'or'
                } else {
                    var query = 'empty'
                }
                if(query == 'empty') {
                    var textConditionLabel = this.form.filter.filter + " is empty "
                } else {
                    var textConditionLabel = this.form.filter.filter + " with " + this.searchfilterEmailMoreOption.join(", " + query +' ')
                }
                oldformula = this.form.filter.filter_option
                this.form.filterOption = this.queryType

            }
            
            if(this.form.filter.filter_type == 'phone'){
                var textCondition = this.searchfilterPhoneMoreOption.join(", ")
                if(this.queryType == 'all'){
                    var query = 'and'
                }else if(this.queryType == 'any'){
                    var query = 'or'
                } else {
                    var query = 'empty'
                } 
                if(query == 'empty') {
                    var textConditionLabel = this.form.filter.filter + " is empty "
                } else {
                    var textConditionLabel = this.form.filter.filter + " with " + this.searchfilterPhoneMoreOption.join(", " + query +' ')
                }

                oldformula = this.form.filter.filter_option
                this.form.filterOption = this.queryType
                
            }
            this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                'type' : this.form.filter.filter_type,
                'condition' : this.form.filter.filter_key,
                'conditionText' : this.form.filter.filter,
                'formula' : this.form.filterOption,
                'textCondition' : textCondition ,
                'oldformula' : oldformula,
                'textConditionLabel' : textConditionLabel,
                'api' : api,
            }                    
            this.form.filterText = ''
            this.form.dropdown = ''
            this.filter = false
            this.filterEmail = false
            this.filterPhone = false
            this.filterInput = false
            this.filterDropdown = false
            this.filterDateRange = false
            this.form.filter = ''
            this.showInputTextOrDropdown = true
            this.queryType = ""
            this.searchfilterEmailMoreOption = []
            this.searchfilterPhoneMoreOption = []
            this.getDatasetsCall(1)
        },        
        showFilterDetails(filter, index){
            this.showView = false
            this.offset('.filterbtn-'+index, 0);
            if(filter.type == 'textbox'){
                this.form.filter = filter.conditionText                
                this.form.filterOption = filter.formula
                this.form.filterText = filter.textCondition
                this.filterOptions = []
                this.filterOptions = filter.oldformula.split(",")
                this.filter = true
                this.filterInput = true
                this.filterDropdown = false
                this.filterDateRange = false
                this.filterEmail = false
                this.filterPhone = false
            } else if(filter.type == 'dropdown'){
                this.form.filter = filter.conditionText
                this.form.filterOption = filter.formula
                this.form.dropdown = filter.textCondition
                this.filterOptions = []
                this.filterOptions = filter.oldformula.split(",")
                this.filter = true
                this.filterInput = false
                this.filterDropdown = true
                this.filterDateRange = false
                let api = filter.api
                this.selectedOptions = []
                this.selectedOptionsId = []
                axios.get(api).then((response) => {
                    this.selects = response.data.results;                    
                    
                    var newRecords = filter.textCondition.split(",")
                    for(var i = 0; i <= newRecords.length; i++){
                        var newStage = this.selects.filter(function(e){
                            return e.oid == newRecords[i]
                        })
                        if(newStage.length > 0){
                            this.selectedOptionsId.push(newRecords[i])
                            this.selectedOptions.push(newStage[0].name)
                        }
                    }
                    this.filterEmail = false
                    this.filterPhone = false
                });
            } else if(filter.type == 'calendar'){
                this.form.filter = filter.conditionText
                this.form.filterOption = filter.formula
                this.form.filterText = filter.textCondition
                this.filterOptions = []
                this.filterOptions = filter.oldformula.split(",")
                this.filter = true
                this.filterInput = false
                this.filterDropdown = false
                this.filterDateRange = true
                this.filterEmail = false
                this.filterPhone = false
            } else if(filter.type == 'email'){
                this.form.filter = filter.conditionText
                this.form.filterOption = filter.formula
                this.queryType = filter.formula
                this.form.filterText = filter.textCondition
                
                this.bypassFIlterKey = filter.condition
                this.queryType = filter.formula
                this.filterOptions = []
                this.filterOptions = filter.oldformula.split(",")
                this.searchfilterEmailMoreOption = filter.textCondition.replace(/\s+/g, '').split(",")

                this.filter = true
                this.filterInput = false
                this.filterDropdown = false
                this.filterDateRange = false
                this.filterEmail = true
                this.filterPhone = false
            } else if(filter.type == 'phone'){
                this.form.filter = filter.conditionText
                this.form.filterOption = filter.formula
                this.queryType = filter.formula
                this.form.filterText = filter.textCondition
                
                this.bypassFIlterKey = filter.condition
                this.queryType = filter.formula
                this.filterOptions = []
                this.filterOptions = filter.oldformula.split(",")
                this.searchfilterPhoneMoreOption = filter.textCondition.replace(/\s+/g, '').split(",")

                this.filter = true
                this.filterInput = false
                this.filterDropdown = false
                this.filterDateRange = false
                this.filterEmail = false
                this.filterPhone = true
            }
            
            this.filterUpdateBtn = true
            this.filterBtn = false   
            this.showInputTextOrDropdown = true   
            if(this.form.filterOption == 'is empty' || this.form.filterOption == 'is not empty'){
                this.showInputTextOrDropdown = false
            } else {
                this.showInputTextOrDropdown = true
            }      
        },
        updateFilter(){
            for(const i in this.form.filterConditionsArray){
                
                if(this.form.filterConditionsArray[i]["conditionText"] == this.form.filter || this.form.filterConditionsArray[i]["condition"] == this.bypassFIlterKey){
                    
                    if(this.form.filterConditionsArray[i]["type"] == "textbox"){
                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        this.form.filterConditionsArray[i]["textCondition"] = this.form.filterText
                        if(this.form.filterOption == 'is empty'){
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter.charAt(0).toUpperCase()+ this.form.filter.slice(1) +' '+ this.form.filterOption
                        } else {
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter.charAt(0).toUpperCase()+ this.form.filter.slice(1) +' '+ this.form.filterOption +' '+ this.form.filterText
                        }
                        
                        
                    } else if(this.form.filterConditionsArray[i]["type"] == "dropdown"){

                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        if(this.selectedOptionsId.length == 0){
                            this.form.filterConditionsArray[i]["textCondition"] = this.form.dropdown
                        }else{
                            this.form.filterConditionsArray[i]["textCondition"] = this.selectedOptionsId.join(',')
                        }
                        if(this.form.filterOption == 'is empty' || this.form.filterOption == 'is not empty'){
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +' '+ this.form.filterOption
                        } else {
                            if(this.selectedOptionsId.length == 0){
                                var f = this.form.dropdown
                                var s = this.selects.filter(function(e){
                                    return e.oid == f
                                })
                                this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +' '+ this.form.filterOption +' '+s[0]["name"]
                            }else{
                                this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +' '+ this.form.filterOption +' '+ this.selectedOptions.join(', ')
                            }
                        }
                        
                    } else if(this.form.filterConditionsArray[i]["type"] == "calendar"){

                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        this.form.filterConditionsArray[i]["textCondition"] = this.form.filterText
                        var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                        var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                        this.form.filterConditionsArray[i]['textCondition'] = startDate+'--'+endDate
                        this.form.filterConditionsArray[i]['textConditionLabel'] = startDate.substring(0,10) 

                    } else if(this.form.filterConditionsArray[i]["type"] == "email"){
                        this.form.filterConditionsArray[i]["formula"] = this.queryType
                        this.form.filterConditionsArray[i]["textCondition"] = this.searchfilterEmailMoreOption.join(", ")

                        if(this.queryType == 'all'){
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +  " with " + this.searchfilterEmailMoreOption.join(",  and ")
                        }else if(this.queryType == 'any'){
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +  " with " + this.searchfilterEmailMoreOption.join(",  or ")
                        } else {
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +  " is empty "
                        }

                        

                    } else if(this.form.filterConditionsArray[i]["type"] == "phone"){
                        this.form.filterConditionsArray[i]["formula"] = this.queryType
                        this.form.filterConditionsArray[i]["textCondition"] = this.searchfilterPhoneMoreOption.join(", ")

                        if(this.queryType == 'all'){
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +  " with " + this.searchfilterPhoneMoreOption.join(",  and ")
                        }else{
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +  " with " + this.searchfilterPhoneMoreOption.join(",  or ")
                        }

                        
                    }
                    this.form.filterText = ''
                    this.form.filter = ''
                    this.form.dropdown = ''
                    this.filter = false
                    this.filterInput = false
                    this.filterDropdown = false
                    this.filterDateRange = false
                    this.filterBtn = true
                    this.filterUpdateBtn = false
                    this.showInputTextOrDropdown = true
                    this.searchfilterEmailMoreOption = []
                    this.searchfilterPhoneMoreOption = []
                    this.selectedOptions = []
                    this.selectedOptionsId = []
                    this.queryType = ""
                    this.bypassFIlterKey = ''
                    this.getDatasetsCall(1)
                }
            }
        },
        removeFilter(index){
            this.form.filterConditionsArray.splice(index, 1)
            this.filterBtn = true
            this.showView = false
            this.filter = false
            this.getDatasetsCall(1);
        },        
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        MumberFormated(numbr) {
            return this.$options.filters.phoneFormatting(numbr);
        },
        myDateFormat(txt, val) {
            return txt+' '+this.$options.filters.convertInDayMonth(val)+' ago';
        },
        getFilterData(){
            this.form.sortType = 'outreach_touched_at'
            this.form.sortBy = 'asc'
            this.totalNumberOfRecords = '-';    
            this.getDatasetsCall(1);
        },
        offset(dv, es) {
            var el = document.querySelector(dv); 
            var rect = el.getBoundingClientRect();
            var el2 = document.querySelector('.filter-btns-holder'); 
            var rect2 = el2.getBoundingClientRect();
            var fleft = rect.left - rect2.left - es;
            var ftop = rect.top - rect2.top + 32;
            this.leftpos = fleft+'px';
            this.toppos = ftop+'px';
        },
        updateSorting(by) {
            this.form.sortBy = by;
            this.getDatasetsCall(1);
        },
        refreshAll() {
            this.getDatasetsCall(1);
        },
        showDispo(rid) {
            this.dispo_data = '';
            this.loader = true
            $('#dispoModal').modal('show');
            axios.get('/api/get-record-disposition/'+rid).then((resposne) => {
                this.dispo_data = resposne.data
                this.loader = false
            })
        },
        incCompledSteps(){
            if(this.completedSteps < this.totalSteps - 2){
                this.completedSteps =  this.completedSteps  + 1
            }else{
                return false;
            }
        },
        getDatasetsCall(page) {
            this.loader = true;
            this.$Progress.start();  
            this.form.page = page
            this.form.post('/api/dataset-values-data-call').then((response) => {                
                this.records = response.data;
                this.totalRecords = response.data.total;                
                this.$Progress.finish();               
                this.totalNumberOfRecords = response.data.total;
                this.loader = false;
            });
        },
        getNumbers() {
            this.form.post('/api/called-numbers').then((response) => {
                let obj = response.data;
                this.called_numbers = Object.keys(obj).map((key) => parseInt(obj[key]));
                this.called = true
            });
        },
        StartExport(){
            this.totalSteps = this.recordContainer.length
            this.completedSteps = 1
            this.showProgress = true
            window.setInterval(() => {
                this.incCompledSteps();
            }, 800)
            this.exportForm.exports = this.recordContainer
            this.exportForm.called_numbers = this.called_numbers
            this.exportForm.number_type = this.form.number_type
            this.exportForm.acw = this.form.acw
            this.exportForm.acw_plus = this.form.acw_plus
            this.exportForm.agentName = this.form.agentName
            this.exportForm.disposition = this.form.disposition
            this.exportForm.edate = this.form.edate
            this.exportForm.sdate = this.form.sdate
            this.exportForm.talk_time = this.form.talk_time
            this.exportForm.list = this.form.list
            this.exportForm.talk_time_plus = this.form.talk_time_plus
            this.exportForm.post('/api/get-record-container-info-call').then((response) => {
                this.completedSteps = 0
                this.totalSteps = 0
                this.showProgress = false
                this.fivenineData = response.data
                this.step = 1
                this.loader = false
            })
        },
        selectAllRecords()
        {
            this.loader = true;
            this.$Progress.start();  
            this.form.page = 1
            this.form.post('/api/dataset-values-data-call-all').then((response) => {
                var records = response.data
                this.recordContainer = []
                for(var i = 0; i < records.length; i++){
                    if( (this.recordContainer.indexOf(parseInt(records[i])) == -1) ){
                        this.recordContainer.push(records[i]);
                    }
                }
                this.loader = false;
                this.$Progress.finish();
            });
        },
        showProspect(record_id) {
            this.loading = true;
            $('#prospectModal').addClass('show-sidebar');
            axios.get('/api/get-prospect-email-details/'+record_id).then((response) => { 
                this.prospect = response.data.contact.first_name + ' ' + response.data.contact.last_name
                this.allemails = response.data.allemails,
                this.singleemails = response.data.singleemails,
                this.sequenceemails = response.data.sequenceemails,
                this.loading = false;
            })
        },
        closeSideBar() {
            this.prospect = '';
            $('#prospectModal').removeClass('show-sidebar');
        },
    },
    mounted(){
        this.getNumbers();
    },
    beforeMount() {
        
    },
    created() {
        const obj = this.$route.query;
        this.form.acw = obj.acw
        this.form.acw_plus = obj.acw_plus
        this.form.agentName = obj.agentName
        this.form.disposition = obj.disposition.split(',')
        this.form.edate = obj.edate
        this.form.sdate = obj.sdate
        this.form.talk_time = obj.talk_time
        this.form.list = obj.list
        this.form.talk_time_plus = obj.talk_time_plus
        this.form.number_type = obj.number_type
        this.getDatasetsCall(1);
        if(this.filterItems == '') {
           this.$store.dispatch('setDatasetFilter');
        }
    }
}
</script>