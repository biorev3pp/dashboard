<template>
    <div class="pt-2 pl-2">
        <div class="wizard-circle wizard">
            <button type="button" class="float-left btn btn-sm btn-secondary theme-btn icon-btn-left" @click="goBack"  v-if="active_step <= 4 && startnow == false"><i class="bi bi-arrow-left"></i> Back</button> 
            <button type="button" class="float-right btn btn-sm btn-primary theme-btn icon-btn-right mr-2" @click="nextStep" v-if="active_step < 4">Next <i class="bi bi-arrow-right"></i></button> 
            <div class="steps clearfix">
                <ul role="tablist">
                    <li role="tab" class="first" :class="[(done_steps.indexOf(1) >= 0)?'done':(active_step == 1)?'current':'']">
                        <a id="steps-1" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-list-check"></i>
                            </span> 
                            <b>{{ recordCount }} </b> Records Found
                        </a>
                    </li>
                    <li role="tab" class="" :class="[(done_steps.indexOf(2) >= 0)?'done':(active_step == 2)?'current':'disabled']">
                        <a id="steps-2" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-pencil-square"></i>
                            </span>
                            Set List(s) Action 
                        </a>
                    </li>
                    <li role="tab" class="" :class="[(done_steps.indexOf(3) >= 0)?'done':(active_step == 3)?'current':'disabled']">
                        <a id="steps-3" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-check2-all"></i>
                            </span> Refine
                        </a>
                    </li>
                    <li role="tab" class="last" :class="[(done_steps.indexOf(4) >= 0)?'done':(active_step == 4)?'current':'disabled']">
                        <a id="steps-4" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-receipt"></i>
                            </span> Overview
                        </a>
                    </li>
                    <li role="tab" class="last" :class="[(done_steps.indexOf(5) >= 0)?'done':(active_step == 5)?'current':'disabled']">
                        <a id="steps-5" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-hand-thumbs-up"></i>
                            </span> Status
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="position-relative wizard-content bg-white" :class="[(active_step >= 2)?'border mr-2':'']">
            <div class="table-responsive" v-if="active_step == 1">
                <div class="divtable border-top" v-if="recordCount >= 1">
                    <div class="divthead">
                        <div class="divthead-row">
                            <div class="divthead-elem wf-60">SNo</div>
                            <div class="divthead-elem wf-100">Record ID</div>
                            <div class="divthead-elem wf-350">Name</div>
                            <div class="divthead-elem wf-150">Called No</div>
                            <div class="divthead-elem mwf-200">
                                <span class="d-inline-block wf-25 mr-2" v-title="'Dial Attempts'">DA</span>
                                <span class="d-inline-block wf-180">Timestamp</span>
                                <span class="d-inline-block wf-120">Disposition</span>
                                <span class="d-inline-block wf-120">Agent Name</span>
                                <span class="d-inline-block wf-180">List Name </span>
                            </div>
                            <div class="divthead-elem wf-80">Action</div>
                        </div>
                    </div>
                    <div class="divtbody custom-height">
                        <div class="divtbody-row" v-for="(record, rkey) in records" :key="'all-'+rkey">
                            <div class="divtbody-elem wf-60">{{ rkey+1 }}</div>
                            <div class="divtbody-elem wf-100 ">{{ record.record_id }}</div>
                            <div class="divtbody-elem wf-350">
                                <a href="javascript:void(0)" @click="showProspect(record.record_id)" class="text-capitalize">
                                    <b>{{ record.customer_name }}</b>
                                </a> <br> 
                                <small class="fw-500">{{ record.title }} </small> in <span class="company-sm">{{ record.company }}</span>
                            </div>
                            <div class="divtbody-elem wf-150 bg-dark1">
                                <span v-if="numberType">
                                    <b class="d-inline-block position-relative badge badge-dark" style="top:-2px" v-if="numberType">{{numberType}}</b>
                                    <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> 
                                    {{ record.dnis }} </span>
                                </span>
                                <span v-else>
                                    <span v-if="record.number1" class="mb-1" style="line-height:20px">
                                        <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N1</b>
                                        <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number1 }} </span>
                                    </span>
                                    <span v-if="record.number2" class="mb-1" style="line-height:20px">
                                        <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N2</b>
                                        <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number2 }} </span>
                                    </span>
                                    <span v-if="record.number3" class="mb-1" style="line-height:20px">
                                        <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N3</b>
                                        <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number3 }} </span>
                                    </span>
                                </span>
                            </div>
                            <div class="divtbody-elem mwf-200">
                                <div class="innter-elem" v-for="(cd, ck) in record.call_details" :key="ck">
                                    <span class="position-relative ml-0 my-0 wf-25 d-inline-block badge mr-2" :class="[(cd.dial_attempts == 1)?'badge-success':(cd.dial_attempts < 10)?'badge-warning':'badge-danger']">{{ cd.dial_attempts }}</span>
                                    <span class="text-truncate wf-180" v-title="cd.timestamp">{{ cd.timestamp }}</span>
                                    <span class="text-truncate wf-120" v-title="cd.disposition">{{ cd.disposition }}</span>
                                    <span class="text-truncate wf-120" v-title="cd.agent_name"> {{ (cd.agent_name)?cd.agent_name:'No Agent' }}</span>
                                    <span class="text-truncate wf-180" v-title="cd.list_name"> {{ cd.list_name }}</span>
                                </div>
                            </div>
                            <div class="divtbody-elem wf-80">
                                <button class="btn btn-sm btn-danger" type="button" @click="deleteRecord(rkey)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="alert alert-info" v-else>
                    No records found.
                </p>
            </div>
            <div class="p-3" v-else-if="active_step == 2">
                <div class="text-center p-2"  style="margin:auto">
                    <div class="wf-300 d-inline-block my-2" style="margin:auto">
                        <span class="btn theme-btn" :class="[(form.action == 'create')?'btn-dark':'btn-outline-dark']" @click="setAction('create')"> Create New List </span>
                    </div>
                    <div class="wf-300 d-inline-block my-2" style="margin:auto">
                        <span class="btn theme-btn" :class="[(form.action == 'update')?'btn-dark':'btn-outline-dark']" @click="setAction('update')"> Update List(s) </span>
                    </div>
                    <div class="wf-300 d-inline-block my-2" style="margin:auto">
                        <!-- <span class="btn theme-btn" :class="[(form.action == 'delete')?'btn-dark':'btn-outline-dark']" @click="setAction('delete')"> Delete From List(s) </span> -->
                        <div class="form-check">
                            <input class="form-check-input invisible" type="checkbox" id="delete-checkbox" @click="deleteCheckbox" >
                            <label class="btn theme-btn m-0" for="delete-checkbox" :class="[(showCheckboxDelete)?'btn-danger':'btn-outline-danger']">
                               Delete From List(s)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row m-0">
                    <div class="col-sm-8 col-12" v-if="form.action == 'create'">
                        <div class="text-center p-4">
                            <label for="list_name" class="text-uppercase fw-500">List Name</label>
                            <input type="text" v-model="form.list_name" id="list_name" class="form-control wf-300" placeholder="Enter name of new list" style="margin:auto">
                        </div>
                    </div>
                    <div class="col-sm-8 col-12" v-else-if="form.action == 'update'">
                        <div class="text-center p-4">
                            <div class="row m-0">
                                <div class="col-sm-6 col-12">
                                    <h5 class="mb-3 d-inline-block mr-2">Matched List(s) with these Contacts</h5>
                                    <button @click="deMSelectAll" class="btn-btn-sm btn-warning theme-btn" type="button" v-if="isMListExist == true"> Deselect All </button>
                                    <button @click="selectMAll" class="btn-btn-sm btn-primary theme-btn" type="button" v-else> Select All </button>
                                    <div v-if="loader">
                                        <p class="text-center">
                                            <img :src="loader_url" alt="loading.."><br>
                                            Please wait, we are getting Five9 Lists
                                        </p>
                                    </div>
                                    <div class="" style="margin:auto;height: calc(75vh - 135px); overflow-y: auto;    overflow-x: hidden; padding-right:20px" v-else>
                                        <button type="button" class="text-left btn theme-btn btn-block" style="margin:2px" :class="[(form.ac_list.indexOf(lname) >= 0)?'btn-warning':'btn-outline-primary']" @click="setList(lname)" v-for="(lname, lk) in updateableList" :key="'listid'+lk">
                                        {{ lname.name }} 
                                        <span class="float-right"> 
                                            (<b>{{ lname.matched }}</b> matched out of <b>{{ lname.size }}</b>)
                                        </span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <h5 class="mb-3 d-inline-block mr-2">Unmatched List(s) with these Contacts</h5>
                                    <button @click="deUmSelectAll" class="btn-btn-sm btn-warning theme-btn" type="button" v-if="isUmListExist == true"> Deselect All </button>
                                    <button @click="selectUmAll" class="btn-btn-sm btn-primary theme-btn" type="button" v-else> Select All </button>
                                    <div v-if="loader">
                                        <p class="text-center">
                                            <img :src="loader_url" alt="loading.."><br>
                                            Please wait, we are getting Five9 Lists
                                        </p>
                                    </div>
                                    <div class="" style="margin:auto;height: calc(75vh - 135px); overflow-y: auto;    overflow-x: hidden; padding-right:20px" v-else>
                                        <button type="button" class="text-left btn theme-btn btn-block" style="margin:2px" :class="[(form.ac_list.indexOf(lname) >= 0)?'btn-warning':'btn-outline-primary']" @click="setList(lname)" v-for="(lname, lk) in five9_umlist" :key="'listid'+lk">
                                        {{ lname.name }} ({{ lname.size }})
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-12" v-show="showCheckboxDelete">                        
                        <div class="text-center p-4">
                            <h5 class="mb-3 d-inline-block mr-2">Matched List(s) with these Contacts</h5>
                            <button @click="deMSelectAllDelete" class="btn-btn-sm btn-warning theme-btn" type="button" v-if="isMListExistDelete == true"> Deselect All </button>
                            <button @click="selectMAllDelete" class="btn-btn-sm btn-primary theme-btn" type="button" v-else> Select All </button>
                            <div v-if="loader">
                                <p class="text-center">
                                    <img :src="loader_url" alt="loading.."><br>
                                    Please wait, we are getting Five9 Lists
                                </p>
                            </div>
                            <div class="" style="margin:auto;height: calc(75vh - 135px); overflow-y: auto;    overflow-x: hidden; padding-right:20px" v-else>
                                <button type="button" class="text-left btn theme-btn btn-block" style="margin:2px" :class="[(form.ac_listDelete.indexOf(lname) >= 0)?'btn-warning':'btn-outline-primary']" @click="setListDelete(lname)" v-for="(lname, lk) in deleteableList" :key="'listid'+lk">
                                {{ lname.name }} 
                                <span class="float-right"> 
                                    (<b>{{ lname.matched }}</b> matched out of <b>{{ lname.size }}</b>)
                                </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="active_step == 3">
                <div v-if="loader">
                    <p class="text-center">
                        <img :src="loader_url" alt="loading.."><br>
                        Please wait, we are checking prospects with call logs
                    </p>
                </div>
                <div v-else>
                    <div class="row mx-0 my-2" v-show="refinedview == true">
                        <div class="col-6 fs-15">
                            Total Importable Records : <b>  {{ selectedRecords.length }} / {{ form.records.length  }} </b>
                        </div>
                        <div class="col-6 text-right">
                          <select v-model="form.select" v-show="false">
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="divtable border-top" v-if="recordCount >= 1">
                        <div class="divthead">
                            <div class="divthead-row">
                                <div class="divthead-elem wf-60">SNo</div>
                                <div class="divthead-elem wf-100">Record ID</div>
                                <div class="divthead-elem wf-350">Name</div>
                                <div class="divthead-elem wf-150">Called No</div>
                                <div class="divthead-elem mwf-200">
                                    <span class="d-inline-block wf-25 mr-2" v-title="'Dial Attempts'">DA</span>
                                    <span class="d-inline-block wf-180">Timestamp</span>
                                    <span class="d-inline-block wf-120">Disposition</span>
                                    <span class="d-inline-block wf-120">Agent Name</span>
                                    <span class="d-inline-block wf-180">List Name </span>
                                </div>
                                <div class="divthead-elem wf-80">Action</div>
                            </div>
                        </div>
                        <div class="divtbody custom-height-220 divtbody-refined" v-if="refinedview == true">
                            <div class="divtbody-row" v-for="(record, rkey) in form.records" :key="'all-'+rkey" :class="[(duplicateNumbers.indexOf(record.dnis)%2 == 0)?'op1':(duplicateNumbers.indexOf(record.dnis)%2 == 1)?'op2':'']">
                                <div class="divtbody-elem wf-60">
                                    <i v-if="record.dstatus == 1" class="bi cursor-pointer" :class="[(record.enable == 1)?'bi-circle-fill text-success':'bi-circle text-dark']" @click="toggleSelection(record, rkey)"></i>
                                    <i v-else class="bi cursor-pointer" :class="[(record.enable == 1)?'bi-check-square-fill text-success':'bi-square text-dark']" @click="toggleSelection(record, rkey)"></i>
                                </div>
                                <div class="divtbody-elem wf-100 ">{{ record.record_id }}</div>
                                <div class="divtbody-elem wf-350">
                                    <a href="javascript:void(0)" @click="showProspect(record.record_id)" class="text-capitalize">
                                        <b>{{ record.customer_name }}</b>
                                    </a> <br> 
                                    <small class="fw-500">{{ record.title }} </small> in <span class="company-sm">{{ record.company }}</span>
                                </div>
                                <div class="divtbody-elem wf-150 bg-dark1">
                                    <span v-if="numberType">
                                        <b class="d-inline-block position-relative badge badge-dark" style="top:-2px" v-if="numberType">{{numberType}}</b>
                                        <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> 
                                        {{ record.dnis }} </span>
                                    </span>
                                    <span v-else>
                                        <span v-if="record.number1" class="mb-1" style="line-height:20px">
                                            <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N1</b>
                                            <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number1 }} </span>
                                        </span>
                                        <span v-if="record.number2" class="mb-1" style="line-height:20px">
                                            <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N2</b>
                                            <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number2 }} </span>
                                        </span>
                                        <span v-if="record.number3" class="mb-1" style="line-height:20px">
                                            <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N3</b>
                                            <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number3 }} </span>
                                        </span>
                                    </span>
                                </div>
                                <div class="divtbody-elem mwf-200">
                                    <div class="innter-elem" v-for="(cd, ck) in record.call_details" :key="ck">
                                        <span class="position-relative m-0 wf-25 d-inline-block badge" :class="[(cd.dial_attempts == 1)?'badge-success':(cd.dial_attempts < 10)?'badge-warning':'badge-danger']">{{ cd.dial_attempts }}</span>
                                        
                                        <span class="text-truncate wf-180" v-title="cd.timestamp">{{ cd.timestamp }}</span>
                                        
                                        <span class="text-truncate wf-120" v-title="cd.disposition">{{ cd.disposition }}</span>
                                        
                                        <span class="text-truncate wf-120" v-title="cd.agent_name"> {{ (cd.agent_name)?cd.agent_name:'No Agent' }}</span>
                                        
                                        <span class="text-truncate wf-180" v-title="cd.list_name"> {{ cd.list_name }}</span>
                                    </div>
                                </div>
                                <div class="divtbody-elem wf-80">
                                    <button class="btn btn-sm btn-danger" type="button" @click="deleteFRecord(rkey)">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="divtbody custom-height" v-else>
                            <div class="divtbody-row" v-for="(record, rkey) in records" :key="'all-'+rkey">
                                <div class="divtbody-elem wf-60">{{ rkey+1 }}</div>
                                <div class="divtbody-elem wf-100 ">{{ record.record_id }}</div>
                                <div class="divtbody-elem wf-350">
                                    <a href="javascript:void(0)" @click="showProspect(record.record_id)" class="text-capitalize">
                                        <b>{{ record.customer_name }}</b>
                                    </a> <br> 
                                    <small class="fw-500">{{ record.title }} </small> in <span class="company-sm">{{ record.company }}</span>
                                </div>
                                <div class="divtbody-elem wf-150 bg-dark1">
                                    <span v-if="numberType">
                                        <b class="d-inline-block position-relative badge badge-dark" style="top:-2px" v-if="numberType">{{numberType}}</b>
                                        <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> 
                                        {{ record.dnis }} </span>
                                    </span>
                                    <span v-else>
                                        <span v-if="record.number1" class="mb-1" style="line-height:20px">
                                            <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N1</b>
                                            <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number1 }} </span>
                                        </span>
                                        <span v-if="record.number2" class="mb-1" style="line-height:20px">
                                            <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N2</b>
                                            <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number2 }} </span>
                                        </span>
                                        <span v-if="record.number3" class="mb-1" style="line-height:20px">
                                            <b class="d-inline-block position-relative badge badge-dark wf-25" style="top:-2px">N3</b>
                                            <span class="wf-75 text-justify d-inline-block"  style="text-justify:inter-word"> {{ record.number3 }} </span>
                                        </span>
                                    </span>
                                </div>
                                <div class="divtbody-elem mwf-200">
                                    <div class="innter-elem" v-for="(cd, ck) in record.call_details" :key="ck">
                                        <span class="position-relative m-0 wf-25 d-inline-block badge" :class="[(cd.dial_attempts == 1)?'badge-success':(cd.dial_attempts < 10)?'badge-warning':'badge-danger']">{{ cd.dial_attempts }}</span>
                                        
                                        <span class="text-truncate wf-180" v-title="cd.timestamp">{{ cd.timestamp }}</span>
                                        
                                        <span class="text-truncate wf-120" v-title="cd.disposition">{{ cd.disposition }}</span>
                                        
                                        <span class="text-truncate wf-120" v-title="cd.agent_name"> {{ (cd.agent_name)?cd.agent_name:'No Agent' }}</span>
                                        
                                        <span class="text-truncate wf-180" v-title="cd.list_name"> {{ cd.list_name }}</span>
                                    </div>
                                </div>
                                <div class="divtbody-elem wf-80">
                                    <button class="btn btn-sm btn-danger" type="button" @click="deleteRecord(rkey)">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 text-center" v-else-if="active_step == 4">
                <h5 class="p-2">
                    Now you are ready to import contacts on Five9. Click on 'Start' button to continue.
                </h5>
                <p class="p-2 wf-300" style="margin:auto" v-if="startnow == false">
                    <button class="btn btn-primary theme-btn" type="button" @click="StartExport"> Start</button>
                </p>
                <div v-if="startnow && form.action == 'create'">
                    <p class="p-3">
                        <img :src="loader_url" alt="Please wait...">
                    </p>
                </div>
                <div v-else-if="startnow && form.action != 'create'">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>List Name</th>
                                <th class="wf-100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(aclst, ak) in  form.ac_list" :key="'fac'+ak">
                                <td class="text-left">
                                    <h5>{{ aclst.name }}</h5>
                                </td>
                                <td> 
                                    <span v-if="donelists.indexOf(aclst.name) >= 0" class="btn btn-sm btn-success theme-btn">
                                        Updated
                                    </span>
                                    <span v-else-if="plist == aclst" class="btn btn-sm btn-primary theme-btn">
                                        Processing
                                    </span>
                                    <span v-else class="btn btn-sm btn-danger theme-btn">
                                        Awaiting
                                    </span>
                                </td>
                            </tr>
                            <tr v-for="(aclst, ak) in  form.ac_listDelete" :key="'faca'+ak">
                                <td class="text-left">
                                    <h5>{{ aclst.name }}</h5>
                                </td>
                                <td> 
                                    <span v-if="donelistsDelete.indexOf(aclst.name) >= 0" class="btn btn-sm btn-success theme-btn">
                                        Updated
                                    </span>
                                    <span v-else-if="plistDelete == aclst" class="btn btn-sm btn-primary theme-btn">
                                        Processing
                                    </span>
                                    <span v-else class="btn btn-sm btn-danger theme-btn">
                                        Awaiting
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-3 text-center" v-else-if="active_step == 5">
                <h5 class="alert alert-info wf-300" style="margin:40px auto 10px">Your import has been completed successfully.</h5>
                <h5 class="alert alert-danger wf-300" style="margin:10px auto"> <b>NOTE: </b> Please, do any other export activity only after {{ time }} minutes.</h5>
                <h5 class="alert alert-warning wf-300" style="margin:10px auto 40px"> You will be able to view the export report in EXPORT HISTORY section after {{ time }} minutes.</h5>
                <a href="/datasets" class="btn theme-btn btn-primary">Click here to continue</a>
            </div>
            <div class="p-3" v-else>
                <p class="alert alert-warning"> Something is missing</p>
            </div>
        </div>
        <div class="fullsidebar" id="prospectModal">
            <div class="sidebar-content">
                <div class="sidebar-header">
                    <h5 class="sidebar-title">Call Details For Prospect - {{ prospect.name }}</h5>
                    <button type="button" class="close" @click="closeSideBar()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="sidebar-body">
                    <p class="text-center p-4" v-if="loading">
                        <img :src="loader_url" alt="loading...">
                    </p>
                    <div v-else>
                        <div>
                            <table class="table table-striped table-bordered mb-2 table-condensed" v-if="prospect.mobilePhone">
                                <tbody>
                                    <tr>
                                        <th colspan="2">Mobile Phone -  {{ prospect.mobilePhone.phone }}</th>
                                        <th colspan="2">Called / Received - {{ prospect.mobilePhone.called }} / {{ prospect.mobilePhone.received }}</th>
                                    </tr>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Dials</th>
                                        <th>Recevied</th>
                                        <th>Disposition</th>
                                    </tr>
                                    <tr v-for="(mrecord, mk) in prospect.mobilePhone.records" :key="'mk'+mk">
                                        <td>{{ mrecord.timestamp | setFulldate }}</td>
                                        <td>{{ mrecord.dial_attempts }}</td>
                                        <td>{{ mrecord.call_received }}</td>
                                        <td>{{ mrecord.disposition }}</td>
                                    </tr>
                                </tbody>
                            </table>  
                            <table class="table table-striped table-bordered mb-2 table-condensed" v-if="prospect.workPhone">
                                <tbody>
                                    <tr>
                                        <th colspan="2">Work Phone - {{ prospect.workPhone.phone }}</th>
                                        <th colspan="2">Called / Received - {{ prospect.workPhone.called }} / {{ prospect.workPhone.received }}</th>
                                    </tr>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Dials</th>
                                        <th>Recevied</th>
                                        <th>Disposition</th>
                                    </tr>
                                    <tr v-for="(wrecord, wk) in prospect.workPhone.records" :key="'wk'+wk">
                                        <td>{{ wrecord.timestamp  | setFulldate }}</td>
                                        <td>{{ wrecord.dial_attempts }}</td>
                                        <td>{{ wrecord.call_received }}</td>
                                        <td>{{ wrecord.disposition }}</td>
                                    </tr>
                                </tbody>
                            </table>  
                            <table class="table table-striped table-bordered mb-2 table-condensed" v-if="prospect.homePhone">
                                <tbody>
                                    <tr>
                                        <th colspan="2">Home Phone - {{ prospect.homePhone.phone }}</th>
                                        <th colspan="2">Called / Received - {{ prospect.homePhone.called }} / {{ prospect.homePhone.received }}</th>
                                    </tr>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Dials</th>
                                        <th>Recevied</th>
                                        <th>Disposition</th>
                                    </tr>
                                    <tr v-for="(hrecord, hk) in prospect.homePhone.records" :key="'hk'+hk">
                                        <td>{{ hrecord.timestamp | setFulldate  }}</td>
                                        <td>{{ hrecord.dial_attempts }}</td>
                                        <td>{{ hrecord.call_received }}</td>
                                        <td>{{ hrecord.disposition }}</td>
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

export default {
    props: {
        'data': [Array, Object],
        'step': Number,
        'ntype': String,
    },
    data() {
        return {
            selectedRecords : [],
            groupedRecods : [],
            ungroupedRecods : [],
            ac_list:[],
            showCheckboxDelete : false,
            five9_mlist:'',
            five9_mlistTest:'',
            five9_mlistDelete:'',
            five9_mlistDeleteTest:'',
            five9_umlist:'',
            five9_umlistTest:'',
            active_step:1,
            done_steps:[],
            next_step:2,
            prev_step:0,
            startnow:false,
            loader:false,
            loading:false,
            loader_url: '/img/spinner.gif',
            donelists:[],
            donelistsDelete:[],
            duplicateNumbers:[],
            plist:'',
            plistDelete:'',
            refinedview:false,
            comparable_list:[],
            time:0,
            prospect:'',

            form: new Form({
                ulist : [],
                dlist : [],
                records:[],
                action: '',
                ac_list:[],
                ac_listDelete:[],
                acp_list:'',
                list_name:'',
                code : '',
                select : 0,
                ntype:'',
                source : 'call'
            })
        }
    },
    computed: {
        numberType() {
            if(this.ntype == 'number1') {
                this.form.ntype = 'N1';
                return 'N1';
            } else if(this.ntype == 'number2') {
                this.form.ntype = 'N2';
                return 'N2';
            } else if(this.ntype == 'number3') {
                this.form.ntype = 'N3';
                return 'N3';
            } else {
                this.form.ntype = '';
                return '';
            }
        },
        selected() {
            let srcnt = this.form.records.filter((ele) => {
                return ele.enabled == 1
            })
            return srcnt.length;
        },
        duplicateCount() {
            return this.form.records.map(e => e['dnis']).map((e, i, final) => final.indexOf(e) !== i && i).filter(obj=> arr[obj]).map(e => arr[e]["dnis"])
        },     
        records() {
            let rdata = (this.data)?this.data:[];
            let ntype = this.ntype
            let refined = [];
            let refined_ids = [];
            rdata.forEach((ele) => {
                if(refined_ids.indexOf(ele.record_id) == -1) {
                    refined_ids.push(ele.record_id);
                    let fdata = new Object();
                    fdata.customer_name = ele.customer_name
					fdata.first_name = ele.first_name
                    fdata.last_name = ele.last_name
                    fdata.record_id = ele.record_id
                    fdata.title = ele.title
                    fdata.company = ele.company
                    fdata.dnis = ele.dnis
                    fdata.number1 = ele.number1
                    fdata.number2 = ele.number2
                    fdata.number3 = ele.number3
                    fdata.call_details = []

                    let cdata = new Object();
                    cdata.dial_attempts = ele.dial_attempts
                    cdata.timestamp = ele.timestamp
                    cdata.list_name = ele.list_name
                    cdata.agent_name = ele.agent_name
                    cdata.disposition = ele.disposition

                    fdata.call_details.push(cdata)
                    refined.push(fdata)
                } else {
                    let orkey = refined_ids.indexOf(ele.record_id)
                    let cdata = new Object();
                    cdata.dial_attempts = ele.dial_attempts
                    cdata.timestamp = ele.timestamp
                    cdata.list_name = ele.list_name
                    cdata.agent_name = ele.agent_name
                    cdata.disposition = ele.disposition
                    refined[orkey].call_details.push(cdata)
                }
            })
            let refinedGroup = [];
            let refined_idsGroup = [];
            refined.forEach((ele) => {
                
                if(refined_idsGroup.indexOf(ele.dnis) == -1) {
                    refined_idsGroup.push(ele.dnis);
                    let fdataGroup = new Object();
                    fdataGroup.dnis = ele.dnis
                    fdataGroup.gcount = 1
                    fdataGroup.prospect_details = []
                    
                    let cdataGroup = new Object();
                    
                    fdataGroup.prospect_details.push(ele)
                    refinedGroup.push(fdataGroup)
                    
                } else {
                    let orkey = refined_idsGroup.indexOf(ele.dnis)
                    let cdataGroup = new Object();
                    
                    refinedGroup[orkey].gcount = Number(refinedGroup[orkey].gcount) + 1
                    refinedGroup[orkey].prospect_details.push(ele)
                }
            })
            this.groupedRecods = _.orderBy(refinedGroup, 'gcount', 'desc')
            return refined;
        },
        recordCount() {
            return Object.keys(this.records).length
        },
        dlength() {
            return this.donelists.length
        },
        isMListExist() {
            if(this.form.dlist.length + this.form.ulist.length == this.ac_list.length){
                if(this.form.ulist.length > 0){
                    return true
                }
                return false
            }else{
                return false
            }
        },
        isMListExistDelete() {
            if(this.form.dlist.length + this.form.ulist.length == this.ac_list.length){
                if(this.form.dlist.length > 0){
                    return true
                }
                return false
            }else{
                return false
            }
        },
        isUmListExist() {
            let cmr = 0;
            let $this = this;
            if(this.five9_umlist == '') { return false} 
            else {
                this.five9_umlist.forEach((ele) => {
                    if($this.form.ac_list.indexOf(ele) >= 0) {
                        cmr++
                    }
                })
            }
            return (this.five9_umlist.length == cmr)?true:false
        },
        updateableList() {
            let $this = this;
            return this.ac_list.filter(function(element){
                if($this.form.dlist.length > 0){
                    if($this.form.dlist.indexOf(element.name) == -1){
                        return element
                    }
                }else{
                    return element
                }
            })
        },
        deleteableList() {
            let $this = this;
            return this.ac_list.filter(function(element){
                if($this.form.ulist.length > 0){
                    if($this.form.ulist.indexOf(element.name) == -1){
                        return element
                    }
                }else{
                    return element
                }
            })
        },
    },
    methods: {
        deleteRecord(rkey) {
            this.data.splice(rkey, 1)
        },
        goBack() {
            if(this.active_step == 1) {
                this.active_step = 0;
                this.done_steps = [];
                this.next_step = this.next_step - 1;
                this.prev_step = this.prev_step - 1;
                this.$parent.step = 0;
            }
            else if(this.active_step >= 2) {
                this.active_step = this.active_step - 1;
                this.done_steps.splice(this.done_steps.indexOf(this.active_step), 1);
                this.next_step = this.next_step - 1;
                this.prev_step = this.prev_step - 1;
            }
        },
        checkStep() {
            if(this.active_step == 3) {
                let $this = this;
                if(this.form.action == 'delete') {
                    return confirm('Are you sure, you want to continue?')
                }
                else {
                    if(this.refinedview == true) {
                        return confirm('Are you sure, you want to continue?')
                    }
                    else {
                        this.$swal({
                            title: 'Please wait! ',
                            text: "We are refining the records ?",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok'
                        }).then((result) => {
                            if(result.value == true) {
                                $this.refinedview = true
                                $this.refinedContacts()
                                return false
                            } 
                        })
                    }
                }
            }
            else if(this.active_step == 2) {
                if(this.form.action == '') {
                    Vue.$toast.error('Please select action to continue.');
                    return false;
                } else if(this.form.action == 'create' && this.form.list_name == '') {
                    Vue.$toast.error('Please enter list name to continue.');
                    return false;
                }
                else if(this.form.action != 'create' && this.form.action != 'update') {
                    Vue.$toast.error('Please select atleast 1 list.');
                    return false;
                } else if(this.form.action != 'create' && this.form.ac_list.length != 0 ||  this.form.ac_listDelete.length == 0) {
                    return true
                } else {
                    return true
                }
            }
            else if(this.active_step == 1) {
                this.form.records = this.records;
                return true;
            }
            
        },
        refinedContacts() {
            let $this = this;
            let records = this.groupedRecods
            records.forEach((ele) => {
                ele.prospect_details.forEach((prospect_ele) => {
                    $this.ungroupedRecods.push(prospect_ele)
                })
            })
            let subrec;
            let newRecords = [];
            this.form.records = this.ungroupedRecods
            if(this.ntype == '') {
                this.form.records.forEach((ele) => {
                    subrec = ele;
                    if(subrec.number1 == '' || subrec.number1 == 0) {
                        // Do nothing
                    } else {
                        let dstatus = this.checkNDuplicacy(subrec.number1)
                        subrec.dstatus = dstatus
                        if(dstatus == 1) {
                            subrec.enable = 0
                            if(this.duplicateNumbers.indexOf(subrec.number1) == '-1'){
                                this.duplicateNumbers.push(subrec.number1)
                            }
                        } else {
                            subrec.enable = 1
                        }
                        if(subrec.number1 == subrec.number2) {
                            subrec.number2 = ''
                        }
                        newRecords.push(subrec);
                    }
                })
            } else {      
                this.form.records.forEach((ele) => {
                    subrec = ele;
                    if(subrec.dnis == '' || subrec.dnis == 0) {
                        // Do nothing
                    } else {
                        let dstatus = this.checkDuplicacy(subrec.dnis)
                        subrec.dstatus = dstatus
                        if(dstatus == 1) {
                            subrec.enable = 0
                            if(this.duplicateNumbers.indexOf(subrec.dnis) == '-1'){
                                this.duplicateNumbers.push(subrec.dnis)
                            }
                        } else {
                            subrec.enable = 1
                        }
                        newRecords.push(subrec);
                    }
                })
            }
            // newRecords.sort((a, b) => (a.dnis > b.dnis) ? 1 : -1)
            // newRecords.sort((a, b) => (a.dstatus != b.dstatus) ? 1 : -1)
            this.form.records = []
            this.form.records = newRecords
			this.selectedRecords = this.form.records.filter((ele) => { return ele.enable == 1 })
        },
        nextStep() {
            if(this.active_step < 4) {
                if(this.checkStep() == true) {
                    this.done_steps.push(this.active_step);
                    this.active_step = this.active_step + 1;
                    this.next_step = this.next_step + 1;
                    this.prev_step = this.prev_step + 1;
                } else {
                    return false;
                }
            }
        },
        setAction(act) {
            this.form.action = act
            if((act == 'create' || act == 'update' || act == 'delete') && (this.five9_mlist == '' || this.five9_mlist.length == 0)) {
                this.getList();
            }
        },
        setSubAction(sact) {
            this.form.action_type = sact
        },
        setList(lname) {
            if(this.form.ac_list.indexOf(lname) >= 0) {
               this.form.ac_list.splice(this.form.ac_list.indexOf(lname), 1)
               this.form.ulist.splice(this.form.ulist.indexOf(lname.name), 1)
            } else {
                this.form.ac_list.push(lname)
                this.form.ulist.push(lname.name)
            }
        },
        setListDelete(lname) {
            if(this.form.ac_listDelete.indexOf(lname) >= 0) {
               this.form.ac_listDelete.splice(this.form.ac_listDelete.indexOf(lname), 1)
               this.form.dlist.splice(this.form.dlist.indexOf(lname.name), 1)
            } else {
                this.form.ac_listDelete.push(lname)
                this.form.dlist.push(lname.name)
            }
        },
        getList() {
            this.loader = true
            this.form.post('/api/get-list-matched-data').then((response) => {
                this.five9_mlist = response.data.matched
                this.five9_mlistTest = response.data.matched
                this.five9_mlistDelete = response.data.matched
                this.five9_mlistDeleteTest = response.data.matched
                this.five9_umlist = response.data.notMatched
                this.five9_umlistTest = response.data.notMatched
                this.ac_list = response.data.matched
                this.loader = false
            })
        },
        StartExport() {
            this.startnow = true
            if(this.form.action == "create"){
                let $this = this;
                this.form.post('/api/export-graph-data-to-fivenine').then((response) => {
                    if(response.data.status == "success"){
                        if(this.showCheckboxDelete){
                            this.form.action = 'delete'
                            $this.form.ac_listDelete.forEach((ele) => {
                                $this.form.acp_listDelete = ele.name
                                $this.plistDelete = ele.name
                                this.form.post('/api/export-graph-data-to-fivenine').then((response) => {
                                    if(response.data.status == "success"){
                                        $this.donelistsDelete.push(response.data.list_name)
                                        if($this.form.ac_listDelete.length == $this.donelistsDelete.length) {
                                            this.endSteps()
                                            axios.get('/api/run-queue');
                                        }
                                    }
                                })
                            })
                        }else{
                            this.active_step = 5;
                            this.done_steps.push(4);
                            this.done_steps.push(5);
                            this.next_step = 6;
                            this.prev_step = 4;
                        }
                    }
                })
                
            }else {
                let $this = this;
                $this.form.ac_list.forEach((ele) => {
                    $this.form.acp_list = ele.name
                    $this.plist = ele.name
                    this.form.post('/api/export-graph-data-to-fivenine').then((response) => {
                        if(response.data.status == "success"){
                            $this.donelists.push(response.data.list_name)
                            if($this.form.ac_list.length == $this.donelists.length) {
                                if(!this.showCheckboxDelete){
                                    this.endSteps()
                                    axios.get('/api/run-queue');
                                }
                            }
                        }
                    })
                })
                if(this.showCheckboxDelete){
                    this.form.action = 'delete'
                    $this.form.ac_listDelete.forEach((ele) => {
                        $this.form.acp_listDelete = ele.name
                        $this.plistDelete = ele.name
                        this.form.post('/api/export-graph-data-to-fivenine').then((response) => {
                            if(response.data.status == "success"){
                                $this.donelistsDelete.push(response.data.list_name)
                                if($this.form.ac_listDelete.length == $this.donelistsDelete.length) {
                                    this.endSteps()
                                    axios.get('/api/run-queue');
                                }
                            }
                        })
                    })
                }
            }
        },
        deMSelectAll() {
            let $this = this
            this.five9_mlist.forEach((ele) => {
                if($this.form.ulist.indexOf(ele.name) != -1) {
                    this.form.ac_list.splice($this.form.ac_list.indexOf(ele), 1)
                    this.form.ulist.splice(this.form.ulist.indexOf(ele.name), 1)
                }
            })
        },
        deMSelectAllDelete() {
            let $this = this
            this.five9_mlistDelete.forEach((ele) => {
                if($this.form.dlist.indexOf(ele.name) != -1) {
                    this.form.ac_listDelete.splice($this.form.ac_listDelete.indexOf(ele), 1)
                    this.form.dlist.splice(this.form.dlist.indexOf(ele.name),1)
                }
            })
        },
        selectMAll() {
            let $this = this
            this.five9_mlist.forEach((ele) => {
                if($this.form.ac_list.indexOf(ele) == -1 && $this.form.dlist.indexOf(ele.name) == -1) {
                    this.form.ac_list.push(ele)
                    this.form.ulist.push(ele.name)
                }
            })
            
        },
        selectMAllDelete() {
            let $this = this
            this.five9_mlistDelete.forEach((ele) => {
                if($this.form.ac_listDelete.indexOf(ele) == -1 && $this.form.ulist.indexOf(ele.name) == -1) {
                    this.form.ac_listDelete.push(ele)
                    this.form.dlist.push(ele.name)
                }
            })
            // $this.form.ac_list = []
        },
        deUmSelectAll() {
            let $this = this
            this.five9_umlist.forEach((ele) => {
                if($this.form.ac_list.indexOf(ele) != -1) {
                    this.form.ac_list.splice($this.form.ac_list.indexOf(ele), 1)
                }
            })
        },
        selectUmAll() {
            let $this = this
            this.five9_umlist.forEach((ele) => {
                if($this.form.ac_list.indexOf(ele) == -1) {
                    this.form.ac_list.push(ele)
                }
            })
        },
        deleteFRecord(rkey) {
            this.form.records.splice(rkey, 1)
        },
        endSteps() {
            this.active_step = 5;
            this.done_steps.push(4);
            this.done_steps.push(5);
            this.next_step = 6;
            this.prev_step = 4;
            this.time = this.form.ac_list.length+5;
            this.form.reset();
        },
        showProspect(record_id) {
            this.loading = true;
            $('#prospectModal').addClass('show-sidebar');
            axios.get('/api/get-short-details/'+record_id).then((response) => { this.prospect = response.data.result; this.loading = false;})
        },
        closeSideBar() {
            this.prospect = '';
            $('#prospectModal').removeClass('show-sidebar');
        },
        checkDuplicacy(n1) {
            let dcount = this.form.records.filter((rec) => { return rec.dnis == n1})
            if(dcount.length >= 2) {
                return 1
            } else { return 0 }
        },
        checkNDuplicacy(n1) {
            let dcount = this.form.records.filter((rec) => { return rec.number1 == n1})
            if(dcount.length >= 2) {
                return 1
            } else { return 0 }
        },
        toggleSelection(record, rk) {
            if(record.dstatus == 1) {
                let arr = this.form.records.filter((ele) => {
                    return (ele.dnis == record.dnis)
                })
                //console.log(arr)
                arr.map((ele) => {
                    ele.enable = 0
                })
                this.form.records[rk].enable = 1
				this.form.select = this.form.select + 1
                if(this.form.select > 3){
                    this.form.select = 0
                }            
             } 
            else if(record.enable == 1) {
                this.form.records[rk].enable = 0
				this.form.records[rk].enable = 0
                this.form.select = this.form.select + 1
                if(this.form.select > 3){
                    this.form.select = 0
                }            
                }
            else {
                this.form.records[rk].enable = 1
				this.form.select = this.form.select + 1
                if(this.form.select > 3){
                    this.form.select = 0
                }            
               }
               this.selectedRecords = this.form.records.filter((ele) => { return ele.enable == 1 })  
        },
        deleteCheckbox(){
            if(document.getElementById('delete-checkbox').checked){
                this.showCheckboxDelete = true
                if(this.ac_list.length == 0){
                    this.getList();
                }
            }else{
                this.showCheckboxDelete = false
            }
        }
    },
    mounted() {
        axios.get('/api/randon-string').then((response) => {
            this.form.code = response.data
        })
    }
}
</script>