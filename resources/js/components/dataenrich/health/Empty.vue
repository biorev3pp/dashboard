<template>
    <div class="dh-sections">
        <div class="dh-headings">            
            <div class="dh-heading-sort">
                <div class="row m-0">
                    <div class="col-10 pl-0">
                        <h5 class="dh-heading-title">Prospects with Empty Fields</h5>
                    </div>
                    <div class="col-2 pr-2">
                        
                    </div>
                </div>
            </div>
            <div class="dh-body-content">
                <div class="dh-body-items" v-if="loader == true">
                    <div class="text-center p-4">
                        <div class="icn-spinner bi-gear text-danger" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="dh-body-items" v-else-if="qtype == 'or'">
                    <div class="dhb-item-group" v-for="(dd, dk) in ddata" :key="'ddata'+dk" :class="[(active_data_key == dk)?'active':'']">
                        <div class="dhb-item-group-heading">
                            {{ dk | capitalize }} (<span :class='dk'>{{ dd.length | freeNumber }}</span>) 
                            <i @click="active_data_key = dk" class="bi" :class="[(active_data_key == dk)?'bi-dash-circle':'bi-plus-circle']"></i>
                            <i @click="showProspect(dd, dk)" class="bi bi-arrow-right-circle mr-2" v-show="active_data_key == dk"></i>
                        </div>
                        <div class="dhb-item-group-content">
                            <p v-for="(dvalue, dvk) in dd" :key="'dval'+dvk">
                                {{ dvalue.first_name+' '+dvalue.last_name }}
                                <b class="float-right">{{ '--' }}</b>
                            </p>
                        </div>
                    </div>                    
                </div>
                <div class="dh-body-items" v-else>                
                    <div class="dhb-item-group active" v-if="ddata">
                        <div class="dhb-item-group-heading">
                            All Fields <b>( {{ ddata.length | freeNumber  }} )</b>
                            <i class="bi bi-dash-circle"></i>
                            <i @click="showProspect(ddata, allFields)" class="bi bi-arrow-right-circle mr-2"></i>
                        </div>
                        <div class="dhb-item-group-content">
                            <p  v-for="(record, rkey) in fields" :key="'all-'+rkey">
                            {{ record | capitalize }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dh-contents">
            <div class="w-100" v-if="loader">
                <div class="text-center p-4">
                    <div class="icn-spinner bi-gear text-danger" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="d-table w-100" v-else-if="mloader">
                <div class="d-table-cell align-middle text-center p-4">
                    <div class="spinner-grow text-secondary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div v-else-if="active_data == ''">
                <p class="text-center p-4 text-danger">Please select a group for detailed display of prospects</p>
            </div>
            <div v-else-if="pdata.length == 0">
                <p class="text-center p-4 text-danger">No data found to display</p>
            </div>
            <div v-else>
                <div class="dh-contents-action">
                    <div class="row m-0">
                        <div class="col-sm-6 col-12">
                            <p>Showing <b>{{ filteredData.length }}</b> prospects out of <b>{{ pdata.length }}</b> </p>
                        </div>
                        <div class="col-sm-6 col-12 ">
                            <div class="text-right" v-show="selectedProspect.length">
                                <i class="bi bi-gear-wide-connected icn-spinner cursor-pointer" @click="showaction = true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="slide-action" :class="[(showaction)? 'slidein-action':'slideout-action']">
                        <div class="float-left outer-icon">
                            <i class="bi bi-gear-wide-connected"></i>
                        </div>
                        <h5 class="text-center my-2 ml-1  mr-4"> {{ selectedProspect.length | freeNumber }} prospects will be modified. </h5>
                        <div class="float-right outer-icon-right">
                            <i class="bi bi-x-circle fs-18 cursor-pointer" @click="showaction=false"></i>
                        </div>
                        <div class="clearfix"></div>
                        <div class="action-btns" v-if="action_status === ''">
                            <div class="action-btn" @click="action_status = 'deleteBtn'">
                                <i class="bi bi-x-octagon"></i>
                                <span> Delete Data </span>
                            </div>
                            <div class="action-btn" @click="beforeUpdate">
                                <i class="bi bi-pencil-square"></i>
                                <span>Update Data </span>
                            </div>
                            <div class="action-btn" @click="action_status = 'transferBtn'">
                                <i class="bi bi-arrow-left-right"></i>
                                <span>Enrich Data </span>
                            </div>
                        </div>
                        <div class="action-data" v-else>
                            <div class="action-title">
                                <i class="bi bi-arrow-left-square cursor-pointer fs-20" @click="action_status = ''"></i>
                                <span class="fs-20 ml-3" v-if="action_status == 'deleteBtn'">Delete Data </span>
                                <span class="fs-20 ml-3" v-else-if="action_status == 'transferBtn'">Enrich Data </span>
                                <span class="fs-20 ml-3" v-else-if="action_status == 'updateBtn'">Update Data </span>
                                <span class="fs-20 ml-3" v-else>No Action Defined </span>
                            </div>
                            <div class="action-content p-2">
                                <div class="pt-2" v-if="action_status == 'deleteBtn'">
                                    <div class="alert alert-warning">
                                        <h4><i class="bi bi-exclamation-triangle-fill"></i> Are you sure?</h4>
                                        <p class="fs-14">By this action, Corresponding column will be set to NULL for the selected record values.<br> And you will not be able to revert it at this moment.</p>
                                        <p class="fs-14">Click on <b>YES</b> button to continue.</p>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <button class="btn theme-btn btn-warning" type="button" @click="action_status = ''">No</button>
                                        </div>
                                        <div class="col-6 text-right">
                                            <button class="btn theme-btn btn-primary" type="button" @click="resetDateAction">Yes</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-2" v-else-if="action_status == 'updateBtn'">
                                    <div class="steps-going-on" :class="[(ubtn_Step == 1)?'active':((ubtn_Step > 1)?'done':'pending')]">
                                        <label class="label-control"><span class="count-badge">1</span> Select Field to be updated from</label>
                                        <div class="disposable-div">
                                            <select v-model="uform.uselectedfrom" class="form-control">
                                                <option value="">Select From List</option>
                                                <option v-for="(f, ind) in ufields" :key="ind" :value="ind">{{ f }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="steps-going-on" :class="[(ubtn_Step == 2)?'active':((ubtn_Step > 2)?'done':'pending')]">
                                        <label class="label-control"><span class="count-badge">2</span> Fields to be updated </label>
                                        <div class="disposable-div">
                                            <div v-for="(f, ind) in ufields" :key="'ftbu'+ind" class="custom-checkbox">
                                                <input type="checkbox" name="uselectedto[]" :id="'ftbu-item'+ind" v-model="uform.uselectedto" :value="f">
                                                <label :for="'ftbu-item'+ind">{{f}}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="steps-going-on" :class="[(ubtn_Step == 3)?'active':((ubtn_Step > 3)?'done':'pending')]">
                                        <label class="label-control"><span class="count-badge">3</span> Select Split Parameters</label>
                                        <div class="disposable-div">
                                            <div class="row m-0">
                                                <div class="col-md-4 pl-0">
                                                    <label for="ignorance" class="label-control">Ignorance Type</label>
                                                    <select class="form-control" v-model="uform.ignoranceType">
                                                        <option>Select</option>
                                                        <option value="1">After String</option>
                                                        <option value="2">Before String</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 p-0">
                                                    <label for="ignorance" class="label-control">Ignorance String</label>
                                                    <input type="text" class="form-control" v-model="uform.ignorance" >
                                                </div>
                                                <div class="col-md-4 pr-0">
                                                    <label for="separator" class="label-control">Separator</label>
                                                    <input type="text" class="form-control" v-model="uform.separator">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="steps-going-on" :class="[(ubtn_Step == 4)?'active':((ubtn_Step > 4)?'done':'pending')]">
                                        <label class="label-control"><span class="count-badge">4</span> Check Updated values</label>
                                        <div class="disposable-div">
                                            <table class="table table-striped table-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th>Fields to be updated</th>
                                                        <th>Fields to be updated from </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(record, index) in uploadedProspects" :key="'row1-' + index" :class="'row1-' + index">
                                                        <td>
                                                            <span class="d-block" v-for="(f, ind) in uform.uselectedto" :key="'field3-'+ind" :class="'field3-'+ind">
                                                            <b>{{ f.replace('_', ' ') | capitalize }}:</b> {{ record[f] }}
                                                            </span>
                                                        </td> 
                                                        <td>{{ record[uform.uselectedfrom] }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row form-group m-0">
                                        <div class="col-4 text-left pl-0 pr-1">
                                            <button type="button" class="theme-btn btn btn-warning btn-sm" @click="ubtn_Step = ubtn_Step - 1" v-if="ubtn_Step >= 2">
                                                Go Back
                                            </button>
                                        </div>
                                        <div class="col-8 text-right pr-0 pl-1">
                                            <button type="button" class="theme-btn btn btn-primary btn-sm" @click="nextOnUpdateBtn">
                                                {{ (ubtn_Step == 4)?'Update Records':(ubtn_Step == 3)?'Check updated Fields':'Next' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-2" v-else-if="action_status == 'transferBtn'">
                                </div>
                                <div class="pt-2" v-else>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="divtable border-top">
                    <div class="divthead">
                        <div class="divthead-row">
                            <div class="divthead-elem wf-50">
                                <i class="bi selector" @click="toggleAllD" :class="[(dallSelected)?'bi-check-square-fill active':(dindeterminate)?'bi-dash-square-fill mactive':'bi-square']"></i>
                            </div>
                            <div class="divthead-elem mwf-150">
                                Name (Company)    
                                <i class="ifilter float-right bi text-success bi-filter-square-fill" @click="startFiltration('name', 'first_name')" v-if="(sort_by == 'first_name' || sort_by == 'title' || sort_by == 'company')"></i>
                                <i class="ifilter float-right bi" @click="startFiltration('name', 'first_name')" :class="[(active_filter == 'name')?'bi-filter-square-fill':'bi-filter-square']" v-else></i>
                                <div class="ifilter-option-list" v-show="active_filter == 'name'">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="name-tab"  @click="startFiltration('name', 'first_name')" data-toggle="tab" href="#name" role="tab" aria-controls="name" aria-selected="true">Name</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="title-tab"  @click="startFiltration('name', 'title')" data-toggle="tab" href="#title" role="tab" aria-controls="title" aria-selected="false">Title</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="company-tab"  @click="startFiltration('name', 'company')" data-toggle="tab" href="#company" role="tab" aria-controls="company" aria-selected="false">Company</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="name" role="tabpanel" aria-labelledby="name-tab">
                                            <div class="ifilter-sortby border-bottom">
                                                <p class="cursor-pointer" @click="sort_by = 'first_name'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'first_name' && sort_dir == 'asc')"></i>
                                                </p>
                                                <p class="cursor-pointer" @click="sort_by = 'first_name'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'first_name' && sort_dir == 'desc')"></i>
                                                </p>
                                            </div>
                                            <div class="ifilter-options">
                                                <h6>Filter By
                                                    <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                                </h6>
                                                <input type="text" class="form-control" v-model="search_sort_filter.first_name" placeholder="Select First Name">
                                                <div class="ifilter-options-items-list">
                                                    <ul>
                                                        <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                            <b v-if="tempForm.start == 1">
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                            </b>
                                                            <b v-else>
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                            </b>
                                                            <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="title" role="tabpanel" aria-labelledby="title-tab">
                                            <div class="ifilter-sortby border-bottom">
                                                <p class="cursor-pointer" @click="sort_by = 'title'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'title' && sort_dir == 'asc')"></i>
                                                </p>
                                                <p class="cursor-pointer" @click="sort_by = 'title'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'title' && sort_dir == 'desc')"></i>
                                                </p>
                                            </div>
                                            <div class="ifilter-options">
                                                <h6>Filter By
                                                    <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                                </h6>
                                                <input type="text" class="form-control" v-model="search_sort_filter.title" placeholder="Select Title">
                                                <div class="ifilter-options-items-list">
                                                    <ul>
                                                        <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                            <b v-if="tempForm.start == 1">
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                            </b>
                                                            <b v-else>
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                            </b>
                                                            <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="company" role="tabpanel" aria-labelledby="company-tab">
                                            <div class="ifilter-sortby border-bottom">
                                                <p class="cursor-pointer" @click="sort_by = 'company'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'company' && sort_dir == 'asc')"></i>
                                                </p>
                                                <p class="cursor-pointer" @click="sort_by = 'company'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'company' && sort_dir == 'desc')"></i>
                                                </p>
                                            </div>
                                            <div class="ifilter-options">
                                                <h6>Filter By
                                                    <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                                </h6>
                                                <input type="text" class="form-control" v-model="search_sort_filter.company" placeholder="Select Company">
                                                <div class="ifilter-options-items-list">
                                                    <ul>
                                                        <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                            <b v-if="tempForm.start == 1">
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                            </b>
                                                            <b v-else>
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                            </b>
                                                            <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ifilter-option-footer text-right">
                                        <button class="btn theme-btn btn-danger mr-1" @click="active_filter = ''">Cancel</button>
                                        <button class="btn theme-btn btn-primary"  @click="active_filter = ''">Done</button>
                                    </div>
                                </div>                        
                            </div>
                            <div class="divthead-elem wf-175">
                                Source 
                                <i class="ifilter float-right bi text-success bi-filter-square-fill" @click="startFiltration('source', 'source')" v-if="(sort_by == 'source')"></i>
                                <i class="ifilter float-right bi" @click="startFiltration('source', 'source')" :class="[(active_filter == 'source')?'bi-filter-square-fill':'bi-filter-square']" v-else></i>
                                <div class="ifilter-option-list" v-show="active_filter == 'source'">
                                    <div class="single-tab tab-content">
                                        <div class="ifilter-sortby border-bottom">
                                            <p class="cursor-pointer" @click="sort_by = 'source'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'source' && sort_dir == 'asc')"></i>
                                            </p>
                                            <p class="cursor-pointer" @click="sort_by = 'source'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'source' && sort_dir == 'desc')"></i>
                                            </p>
                                        </div>
                                        <div class="ifilter-options">
                                            <h6>Filter By
                                                <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                            </h6>
                                            <input type="text" class="form-control"  placeholder="Select source" v-model="search_sort_filter.source" >
                                            <div class="ifilter-options-items-list">
                                                <ul>
                                                    <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                        <b v-if="tempForm.start == 1">
                                                            <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                        </b>
                                                        <b v-else>
                                                            <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                        </b>
                                                        <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ifilter-option-footer text-right">
                                        <button class="btn theme-btn btn-danger mr-1" @click="active_filter = ''">Cancel</button>
                                        <button class="btn theme-btn btn-primary"  @click="updateFilter('source')">Done</button>
                                    </div>
                                </div>                      
                            </div>
                            <div class="divthead-elem wf-150">
                                Stage
                                <i class="ifilter float-right bi text-success bi-filter-square-fill" @click="startFiltration('stage', 'stage_name')" v-if="(sort_by == 'stage_name')"></i>
                                <i class="ifilter float-right bi" @click="startFiltration('stage', 'stage_name')" :class="[(active_filter == 'stage')?'bi-filter-square-fill':'bi-filter-square']" v-else></i>
                                <div class="ifilter-option-list" v-show="active_filter == 'stage'">
                                    <div class="single-tab tab-content">
                                        <div class="ifilter-sortby border-bottom">
                                            <p class="cursor-pointer" @click="sort_by = 'stage_name'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'stage_name' && sort_dir == 'asc')"></i>
                                            </p>
                                            <p class="cursor-pointer" @click="sort_by = 'stage_name'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'stage_name' && sort_dir == 'desc')"></i>
                                            </p>
                                        </div>
                                        <div class="ifilter-options">
                                            <h6>Filter By
                                                <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                            </h6>
                                            <input type="text" class="form-control" v-model="search_sort_filter.stage_name" placeholder="Select Stage">
                                            <div class="ifilter-options-items-list">
                                                <ul>
                                                    <li v-for="(sd, sk) in active_filter_options" :key="'stage'+sk">
                                                        <b v-if="tempForm.start == 1">
                                                            <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                        </b>
                                                        <b v-else>
                                                            <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                        </b>
                                                        
                                                        <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ifilter-option-footer text-right">
                                        <button class="btn theme-btn btn-danger mr-1" @click="active_filter = ''">Cancel</button>
                                        <button class="btn theme-btn btn-primary"  @click="updateFilter('stage_name')">Done</button>
                                    </div>
                                </div>
                            </div>
                            <div class="divthead-elem wf-150">
                                Emails
                                <i class="ifilter float-right bi text-success bi-filter-square-fill" @click="startFiltration('email', 'emails')" v-if="(sort_by == 'emails' || sort_by == 'custom4')"></i>
                                <i class="ifilter float-right bi" @click="startFiltration('email', 'emails')" :class="[(active_filter == 'email')?'bi-filter-square-fill':'bi-filter-square']" v-else></i>
                                <div class="ifilter-option-list" v-show="active_filter == 'email'">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="emails-tab" @click="startFiltration('email', 'emails')" data-toggle="tab" href="#emails" role="tab" aria-controls="emails" aria-selected="true">Business</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="custom4-tab" @click="startFiltration('email', 'custom4')" data-toggle="tab" href="#custom4" role="tab" aria-controls="custom4" aria-selected="false">Supplement</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="emails" role="tabpanel" aria-labelledby="emails-tab">
                                            <div class="ifilter-sortby border-bottom">
                                                <p class="cursor-pointer" @click="sort_by = 'emails'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'emails' && sort_dir == 'asc')"></i>
                                                </p>
                                                <p class="cursor-pointer" @click="sort_by = 'emails'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'emails' && sort_dir == 'desc')"></i>
                                                </p>
                                            </div>
                                            <div class="ifilter-options">
                                                <h6>Filter By
                                                    <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                                </h6>
                                                <input type="text" class="form-control" v-model="search_sort_filter.emails" placeholder="Select Emails">
                                                <div class="ifilter-options-items-list">
                                                    <ul>
                                                        <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                            <b v-if="tempForm.start == 1">
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                            </b>
                                                            <b v-else>
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                            </b>
                                                            <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="custom4" role="tabpanel" aria-labelledby="custom4-tab">
                                            <div class="ifilter-sortby border-bottom">
                                                <p class="cursor-pointer" @click="sort_by = 'custom4'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'custom4' && sort_dir == 'asc')"></i>
                                                </p>
                                                <p class="cursor-pointer" @click="sort_by = 'custom4'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'custom4' && sort_dir == 'desc')"></i>
                                                </p>
                                            </div>
                                            <div class="ifilter-options">
                                                <h6>Filter By
                                                    <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                                </h6>
                                                <input type="text" class="form-control" v-model="search_sort_filter.custom4" placeholder="Select Supplement Email">
                                                <div class="ifilter-options-items-list">
                                                    <ul>
                                                        <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                            <b v-if="tempForm.start == 1">
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                            </b>
                                                            <b v-else>
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                            </b>
                                                            <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ifilter-option-footer text-right">
                                        <button class="btn theme-btn btn-danger mr-1" @click="active_filter = ''">Cancel</button>
                                        <button class="btn theme-btn btn-primary"  @click="active_filter = ''">Done</button>
                                    </div>
                                </div>
                            </div>
                            <div class="divthead-elem wf-150">
                                Phones
                                <i class="ifilter float-right bi text-success bi-filter-square-fill"  @click="startFiltration('phone', 'mobilePhones')" v-if="(sort_by == 'mobilePhones' || sort_by == 'workPhones' || sort_by == 'homePhones')"></i>
                                <i class="ifilter float-right bi"  @click="startFiltration('phone', 'mobilePhones')" :class="[(active_filter == 'phone')?'bi-filter-square-fill':'bi-filter-square']" v-else></i>
                                <div class="ifilter-option-list" v-show="active_filter == 'phone'">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="mobilePhones-tab" @click="startFiltration('phone', 'mobilePhones')" data-toggle="tab" href="#mobilePhones" role="tab" aria-controls="mobilePhones" aria-selected="true">Mobile</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="workPhones-tab" @click="startFiltration('phone', 'workPhones')" data-toggle="tab" href="#workPhones" role="tab" aria-controls="workPhones" aria-selected="false">Work</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="homePhones-tab" @click="startFiltration('phone', 'homePhones')" data-toggle="tab" href="#homePhones" role="tab" aria-controls="homePhones" aria-selected="false">Home</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="mobilePhones" role="tabpanel" aria-labelledby="mobilePhones-tab">
                                            <div class="ifilter-sortby border-bottom">
                                                <p class="cursor-pointer" @click="sort_by = 'mobilePhones'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'mobilePhones' && sort_dir == 'asc')"></i>
                                                </p>
                                                <p class="cursor-pointer" @click="sort_by = 'first_name'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'mobilePhones' && sort_dir == 'desc')"></i>
                                                </p>
                                            </div>
                                            <div class="ifilter-options">
                                                <h6>Filter By
                                                    <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                                </h6>
                                                <input type="text" class="form-control" v-model="search_sort_filter.mobilePhones" placeholder="Select Mobile Phone">
                                                <div class="ifilter-options-items-list">
                                                    <ul>
                                                        <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                            <b v-if="tempForm.start == 1">
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                            </b>
                                                            <b v-else>
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                            </b>
                                                            <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="workPhones" role="tabpanel" aria-labelledby="workPhones-tab">
                                            <div class="ifilter-sortby border-bottom">
                                                <p class="cursor-pointer" @click="sort_by = 'workPhones'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'workPhones' && sort_dir == 'asc')"></i>
                                                </p>
                                                <p class="cursor-pointer" @click="sort_by = 'workPhones'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'workPhones' && sort_dir == 'desc')"></i>
                                                </p>
                                            </div>
                                            <div class="ifilter-options">
                                                <h6>Filter By
                                                    <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                                </h6>
                                                <input type="text" class="form-control" v-model="search_sort_filter.workPhones" placeholder="Select Work Phone">
                                                <div class="ifilter-options-items-list">
                                                    <ul>
                                                        <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                            <b v-if="tempForm.start == 1">
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                            </b>
                                                            <b v-else>
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                            </b>
                                                            <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="homePhones" role="tabpanel" aria-labelledby="homePhones-tab">
                                            <div class="ifilter-sortby border-bottom">
                                                <p class="cursor-pointer" @click="sort_by = 'homePhones'; sort_dir = 'asc';">Sort A <i class="bi bi-arrow-right"></i> Z
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'homePhones' && sort_dir == 'asc')"></i>
                                                </p>
                                                <p class="cursor-pointer" @click="sort_by = 'homePhones'; sort_dir = 'desc';">Sort Z <i class="bi bi-arrow-right"></i> A
                                                    <i class="bi text-success bi-check-circle-fill float-right fs-15" v-show="(sort_by == 'homePhones' && sort_dir == 'desc')"></i>
                                                </p>
                                            </div>
                                            <div class="ifilter-options">
                                                <h6>Filter By
                                                    <small class="cursor-pointer float-right" @click="SelectAllOptions">Select All</small>
                                                </h6>
                                                <input type="text" class="form-control" v-model="search_sort_filter.homePhones" placeholder="Select Home Phone">
                                                <div class="ifilter-options-items-list">
                                                    <ul>
                                                        <li v-for="(sd, sk) in active_filter_options" :key="'source'+sk">
                                                            <b v-if="tempForm.start == 1">
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.out.indexOf(sd) == -1"></i>
                                                            </b>
                                                            <b v-else>
                                                                <i class="bi bi-check-circle text-green" v-show="tempForm.val.indexOf(sd) >= 0"></i>
                                                            </b>
                                                            <span class="d-block text-nowrap text-truncate wf-200" v-title="sd" @click="toggleFromSearch(sd)">{{ sd }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ifilter-option-footer text-right">
                                        <button class="btn theme-btn btn-danger mr-1" @click="active_filter = ''">Cancel</button>
                                        <button class="btn theme-btn btn-primary"  @click="updateFilter('source')">Done</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="divtbody table-without-search">
                        <div class="divtbody-row" v-for="(record, rid) in filteredData" :key="'pdata-'+rid">
                            <div class="divtbody-elem wf-50">
                                <i class="bi selector" :class="[(selectedProspect.indexOf(record.record_id) >= 0)?'bi-check-square-fill active':'bi-square']" @click="toggleOne(record.record_id)"></i>
                            </div>
                            <div class="divtbody-elem mwf-150">
                            <router-link target="_blank" :to="'prospects/' + record.record_id" class="text-capitalize"><b>{{ record.first_name }} {{ record.last_name }} </b></router-link>
                                <br>
                                <small class="fw-500" v-title="record.title" v-if="record.title">{{ record.title }}  in </small> 
                                <span class="company-sm" v-title="record.company" v-if="record.company">{{ record.company }}</span>
                            </div>
                            <div class="divtbody-elem wf-175">
                                {{ record.source }}
                            </div>
                            <div class="divtbody-elem wf-150">
                                <span v-if="record.stage_name" :class="record.stage_css">
                                {{ record.stage_name }}
                                </span>
                                <span class="no-stage" v-else>No Stage</span>
                            </div>
                            <div class="divtbody-elem wf-150">
                                <span class="span-info" :class="[(record.emails)?'show cursor-pointer':'']" v-title="record.emails">BE</span>
                                <span class="span-info" :class="[(record.custom4)?'show cursor-pointer':'']"  v-title="record.custom4">SE</span>
                            </div>
                            <div class="divtbody-elem wf-150">
                                <span class="span-info" :class="[(record.mobilePhones)?'show cursor-pointer':'']" v-title="record.mobilePhones">MP</span>
                                <span class="span-info" :class="[(record.homePhones)?'show cursor-pointer':'']" v-title="record.homePhones">WP</span>
                                <span class="span-info" :class="[(record.workPhones)?'show cursor-pointer':'']" v-title="record.workPhones">HP</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {BFormCheckboxGroup , BFormCheckbox, BFormGroup} from 'bootstrap-vue';

export default {
     components:{BFormCheckboxGroup, BFormCheckbox, BFormGroup},
    props: {
        'qtype': String,
        'loader': Boolean,
        'ddata': [Array, Object],
        'fields': Array
    },
    data() {
        return {
            form: new Form({
                select : 0
            }),
            mloader:false,
            active_data_key:'',
            active_data:'',
            active_field:'',
            pdata:[],
            dallSelected:false,
            dindeterminate:false,
            selectedProspect:[],
            showaction:false,
            showactionResult:false,
            action_status:'',
            active_filter:'',
            active_filter_key:'',
            filter_options:[],
            gcount:0,
            sort_by:'',
            sort_dir:'',
            first_filter:'',
            allFilterOptions:{},
            search_sort_filter:{
                stage_name:'',
                first_name:'',
                title:'',
                company:'',
                mobilePhones:'',
                homePhones:'',
                workPhones:'',
                emails:'',
                source:'',
                custom4:''
            },
            search_sort_filter_data: {},
            tempForm : new Form({
                val : [],
                out : [],
                start: 0
            }),
            ufields : {
                'first_name' : 'First Name', 
                'last_name' : 'Last Name', 
                'emails' : 'Business Email', 
                'company' : 'Company', 
                'title' : 'Title', 
                'mobilePhones' : 'Mobile Phone', 
                'workPhones' : 'Work Phone', 
                'homePhones' : 'Home Phone', 
                'country' : 'Country', 
                'state' : 'State', 
                'city' : 'City', 
                'zip' : 'Zipcode', 
                'timeZone' : 'Timezone', 
                'custom29' : 'Timezone Group', 
                'custom1' : 'Purchase Authorization', 
                'stage' : 'Stage', 
                'custom9' : 'Industry', 
                'custom10' : 'Primary Industry', 
                'custom2' : 'Department', 
            },
            uform: new Form({
                records : [],
                uselectedto : [],
                uselectedfrom : '',
                ignorance : '',
                separator : '',
                ignoranceType : '',
            }),
            urecords : [],
            selectedProspectRecords : [],
            selectedProspectRecordsAfterUpdate : [],
            selectedProspectRecordsAfterUpdateOld : [],
            uploadedProspects : [],
            uploadBtn : false,
            dd : '',
            dk : '',
            ubtn_Step:1,
        }
    },
    computed: {
        allFields(){
            let s = this.fields.map( a => a.charAt(0).toUpperCase() + a.substr(1) );
            return s.join(', ')
            
        },
        filteredData() {
            let xdata = this.pdata
            if(xdata.length == 0) {
                return []
            }
            if(this.sort_by && this.sort_dir) {
                xdata = xdata.sort((a,b) => {
                    if(a[this.sort_by] < b[this.sort_by]) return (this.sort_dir == 'asc')? -1:1;
                    if(a[this.sort_by] > b[this.sort_by]) return (this.sort_dir == 'asc')? 1:-1;
                    return 0;
                });
            } 
            xdata = xdata.filter(element => this.search_sort_filter_data.stage_name.indexOf(element.stage_name) >= 0).filter(element => this.search_sort_filter_data.source.indexOf(element.source) >= 0)
            /* //return xdata
            xdata = xdata.forEach(element => {
                let obk = Object.keys(this.search_sort_filter_data)
                let obe = Object.values(this.search_sort_filter_data)
                return obe.forEach((ele, index) => {   
                    return (ele.indexOf(element[obk[index]]) >= 0)
                }); 
            })  */
            return xdata
        },

        active_filter_options() {
            let xdata = this.filteredData
            let ppdata = this.pdata
            let fset = []
            let ak = this.active_filter_key
            if((this.first_filter == '') || (this.first_filter && this.first_filter == this.active_filter_key)) {
                if(this.search_sort_filter[ak]) {
                    fset = Array.from(new Set(ppdata.map(( obj ) => {return obj[ak]} )));
                    fset = fset.filter((ele) => {
                        return (ele.toLowerCase().includes(this.search_sort_filter[ak].toLowerCase()))
                    })
                } else {
                    fset = Array.from(new Set(ppdata.map(( obj ) => {return obj[ak]} )));
                }
                return fset.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });
            } else {
                if(this.search_sort_filter[ak]) {
                    fset = Array.from(new Set(xdata.map(( obj ) => {return obj[ak]} )));
                    fset = fset.filter((ele) => {
                        return (ele.toLowerCase().includes(this.search_sort_filter[ak].toLowerCase()))
                    })
                } else {
                    fset = Array.from(new Set(xdata.map(( obj ) => {return obj[ak]} )));
                }
                return fset.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });
            }
            
        },
    },
    watch: {
        ddata(newValue, oldValue) {
            if(newValue != oldValue) {
                if(this.qtype == 'or') {
                    this.gcount = newValue.length
                } else {
                    this.gcount = Object.keys(newValue.data).length
                }
            }
            
        },
        qtype(newValue, oldValue) {
            if(newValue != oldValue) {
                this.active_data_key = '';
                this.active_data = '';
                this.active_field = '';
                this.pdata = [];
            }
        },
        selectedProspect(newValue, oldValue) {
            if (newValue.length === 0) {
                this.dindeterminate = false
                this.dallSelected = false
            } 
            else if (newValue.length === this.filteredData.length) {
                this.dindeterminate = false
                this.dallSelected = true
            } else {
                this.dindeterminate = true
                this.dallSelected = false
            }
            this.showaction =  false;
            this.showactionResult = false;
        },
    },
    methods: {
        nextOnUpdateBtn() {
            if(this.ubtn_Step == 1 && this.uform.uselectedfrom == '') {
                Vue.$toast.warning('Please select the field from the list.');
                return false
            }
            if(this.ubtn_Step == 2 && this.uform.uselectedto.length == 0) {
                Vue.$toast.warning('Please select updatable field(s).');
                return false
            }
            if(this.ubtn_Step == 3) {
                if(this.uform.uselectedto.length == 0) {
                    Vue.$toast.warning('Please select split parameters.');
                    return false
                } else {
                    if(this.update() == false) {
                        return false
                    } 
                }
            }
            this.ubtn_Step = this.ubtn_Step  + 1;
        },
        SelectAllOptions() {
            this.tempForm.out = []
        },
        startFiltration(f, fk) {
            this.active_filter = f
            this.active_filter_key = fk
            let obj = Object.entries(this.allFilterOptions)
            let fv = obj.filter(ele => {
                return (ele[0] == fk)
            })
            this.tempForm.val = fv[0][1]
            this.tempForm.out = this.active_filter_options.filter(ele => { return this.tempForm.val.indexOf(ele) == '-1'})
        },
        toggleFromSearch(sv) {
            this.tempForm.start = 1
            if(this.tempForm.out.indexOf(sv) >= 0) {
                this.tempForm.out.splice(this.tempForm.out.indexOf(sv), 1)
            } else {
                this.tempForm.out.push(sv)
            }
        },
        updateFilter(fk) {
            if(this.first_filter == '') {
                this.first_filter = fk;
            }
            this.search_sort_filter_data[this.active_filter_key] = this.active_filter_options.filter(ele => (this.tempForm.out.indexOf(ele) == -1)) 
            
            this.active_filter = '';
            this.active_filter_key = '';
            this.tempForm.val = []
            this.tempForm.start = 0
            this.tempForm.out = []
        },
        updateAllFilter(fk) {
            if(this.first_filter == '') {
                this.first_filter = fk;
            }
            this.search_sort_filter_data[this.active_filter_key] = this.search_sort_filter_data[this.active_filter_key].filter(ele => (this.tempForm.out.indexOf(ele) == -1)) 
            this.active_filter = '';
            this.active_filter_key = '';
            this.tempForm.val = []
            this.tempForm.start = 0
            this.tempForm.out = []
        },
        updateSubFilter(fk) {
            if(this.first_filter == '') {
                this.first_filter = fk;
            }
            this.search_sort_filter_data[this.active_filter_key] = this.search_sort_filter_data[this.active_filter_key].filter(ele => (this.tempForm.out.indexOf(ele) == -1)) 
            this.active_filter = '';
            this.active_filter_key = '';
            this.tempForm.val = []
            this.tempForm.start = 0
            this.tempForm.out = []
        },
        toggleAllD() {
            if(this.selectedProspect.length == this.filteredData.length) {
                this.selectedProspect = []
            } else {
                this.filteredData.forEach(element => {
                    if(this.selectedProspect.indexOf(element.record_id) == '-1') {
                        this.selectedProspect.push(element.record_id)
                    }
                });
            }
            this.resetuform()
        },
        toggleOne(rid){
            if(this.selectedProspect.indexOf(rid) >= 0) {
                this.selectedProspect.splice(this.selectedProspect.indexOf(rid), 1)
            } else {
                this.selectedProspect.push(rid)
            }
            this.resetuform()
        },
        showProspect(dd, dk) {
            this.dd = dd
            this.dk = dk
            this.active_data = dd;
            this.active_field = dk;
            this.mloader = true;
            this.showactionResult = false
            axios.post('/api/get-related-prospectsA', {data:dd}).then((response) => {
                this.pdata = response.data
                this.updateFilterOptions()
                this.mloader = false
            })
        },
        showAndProspect(dd) {
            this.dd = dd
            this.dk = ''
            this.active_data = dd;
            this.mloader = true;
            axios.post('/api/get-related-and-prospectsA', {data:dd}).then((response) => {
                this.pdata = response.data
                this.updateFilterOptions()
                this.mloader = false
            })
        },
        updateFilterOptions() {
            this.first_filter = '';
            let ssfd = {
                stage_name:[],
                first_name:[],
                title:[],
                company:[],
                mobilePhones:[],
                homePhones:[],
                workPhones:[],
                emails:[],
                source:[],
                custom4:[]
            };
            let xdata = [];
            xdata = Array.from(new Set(this.pdata.map(({ stage_name }) => stage_name )));
            ssfd.stage_name = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ source }) => source )));
            ssfd.source = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ company }) => company )));
            ssfd.company = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ first_name }) => first_name )));
            ssfd.first_name = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ title }) => title )));
            ssfd.title = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ mobilePhones }) => mobilePhones )));
            ssfd.mobilePhones = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ homePhones }) => homePhones )));
            ssfd.homePhones = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ workPhones }) => workPhones )));
            ssfd.workPhones = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ emails }) => emails )));
            ssfd.emails = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });

            xdata = Array.from(new Set(this.pdata.map(({ custom4 }) => custom4 )));
            ssfd.custom4 = xdata.sort((a,b) => { if(a < b) return -1; if(a > b) return 1; return 0; });
            this.allFilterOptions = ssfd;
            this.search_sort_filter_data = ssfd;
        
        },
        beforeUpdate(){
            let sp = this.selectedProspect;
            this.selectedProspectRecords = this.pdata.filter((ele) => {
                return (sp.indexOf(ele.record_id) >= 0)
            })
            this.action_status = 'updateBtn'
        },
        resetuform(){
            this.uform.reset()
            this.action_status = ''
            this.showaction =  false;
            this.showactionResult = false;
        },
        update(){
            if(this.uform.uselectedto.length == 0){
                Vue.$toast.warning('<b>Fields to be updated</b> not selected')
                return false
            }
            if(this.uform.uselectedfrom == ''){
                Vue.$toast.warning('<b>Fields to be updated from</b> not selected')
                return false
            }
            if(this.uform.ignoranceType == ''){
                Vue.$toast.warning('Ignorance Type not selected');
                return false
            }
            if(this.uform.ignorance == ''){
                Vue.$toast.warning('Ignorance field is empty');
                return false
            }
            if(this.uform.separator == ''){
                Vue.$toast.warning('Separator field is empty')
                return false
            }
            let spau = []
            // let spauOld = []
            let ignorance  = this.uform.ignorance
            let separator  = this.uform.separator
            let uselectedfrom = this.uform.uselectedfrom
            let uselectedTo = this.uform.uselectedto
            let ignoranceType = this.uform.ignoranceType
            
            let uploadedProspects = []
            this.selectedProspectRecords.forEach((ele) => {
                let  fdata = ele[uselectedfrom]
                let ufdata = new Object();
                if(fdata){
                    let s = null
                    if(ignoranceType == 1){
                        s = fdata.slice(0, fdata.indexOf(ignorance))
                    }else{
                        s = fdata.slice(fdata.indexOf(ignorance)+1, fdata.length)
                    }
                    s = s.split(separator)
                    let spau_ele = []
                    // let spau_ele_old = []
                    uselectedTo.forEach((ele2, index) => {
                        spau_ele[ele2] = s[index]
                        // spau_ele_old[ele2] = ele[ele2]
                        ufdata[ele2] = s[index]
                    })
                    spau_ele[uselectedfrom] = fdata
                    spau.push(spau_ele)
                    ufdata[uselectedfrom] = fdata
                    ufdata.record_id = ele.record_id
                    uploadedProspects.push(ufdata)
                    // spauOld.push(spau_ele_old)
                }
            })
            this.selectedProspectRecordsAfterUpdate = spau
            this.uploadedProspects = []
            this.uploadedProspects = uploadedProspects
            return true
        },
        uploaddata(){
            this.$swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Upload it!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.mloader = true;
                    this.uform.records =  this.uploadedProspects
                    this.uform.post('/api/update-empty-prospects').then((response) => {
                        if(response.data.status == 'success'){
                            Vue.$toast.success("Record(s) uploaded successfully !!");
                            this.resetuform()
                            axios.post('/api/get-uploaded-prospects', {fields: this.fields, qtype : this.qtype, f : this.dk}).then((response) => {
                                    this.ddata = response.data.ddata
                                    this.pdata = response.data.pdata
                                    this.mloader = false;
                            })
                        }
                    })                    
                }
            })
        }
    }
}
</script>