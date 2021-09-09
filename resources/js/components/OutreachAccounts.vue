<template>
    <div>
        <div class="filterbox" @click="filter == false">
            <div class="row m-0">
                <div class="col-md-4 col-12 pl-0">
                    <button type="button" class="btn btn-sm btn-outline-dark" @click="showView=true" v-if="view">{{view.view_name}}</button>
                    <button type="button" class="btn btn-sm btn-outline-dark" @click="showView=true" v-else>Accounts</button>
                    
                    <a href="javascript:void(0)" class="link-primary" v-if="showSaveView" @click="showViewPopup"><i class="bi bi-plus"></i> Save View</a>
                    <a href="javascript:void(0)" class="link-primary" v-if="showSaveView == false" @click="removeview"><i class="bi bi-x"></i></a>

                    <v-select v-if="showView" label="view_name" :options="views" @input="selectView" v-model="view"></v-select>
                </div>
                <div class="col-md-8 col-12 pr-0 text-right form-inline d-block">
                    <span class="mr-2">
                        <label class="form-control  pr-0  border-none"> Sort By : </label>
                        <select class="form-control" v-model="form.sortType" @change="getOutreachAccountLists(1)">
                            <option value="touchedAt">Last Contacted</option>
                            <option value="updatedAt">Last Updated</option>
                            <option value="createdAt">Last Created</option>
                            <option value="name">Name</option>
                            <option value="buyerIntentScore">Buyer Intent Score</option>
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
                        <span class="text-primary mx-1 pointer-hand" @click="showFilterDetails(filter, index)" v-title="filter.textConditionLabel"> {{filter.textConditionLabel }}</span>
                        <i class="bi bi-x pr-1  pointer-hand" @click="removeFilter(index)"></i>
                    </span>
                    <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" @click="showFilter"><i class="bi bi-plus"></i> Add filter</a>
                    <div class="stage-select-box" v-show="filter">
                        <div class="form-group" v-show="filter">
                            <v-select label="filter" :options="filterItems" @input="showFilterOption" v-model="form.filter" :disabled="disableFilterItems"></v-select>
                        </div>
                        <div class="row">
                            <div class="col-md-4 pr-1">
                                <select  class="form-control" v-model="form.filterOption" v-if="(filterInput || filterDropdown) && (filter != null)" @change="makeEmpty">
                                    <option v-for="filterOption in filterOptions" :key="'select-'+filterOption" :value="filterOption">{{ filterOption }}</option>
                                </select>
                            </div>
                            <div class="col-md-8 pl-1">
                                <div v-show="filterInput">
                                    <input type="text" class="form-control" v-model="form.filterText" placeholder="" :readonly="filterTextReadonly">
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
                    </span>
                </div>
                <div class="divtable">
                    <div class="divthead">
                        <div class="divthead-row">
                            <!-- <div class="divthead-elem wf-45 text-center">
                                <input type="checkbox" name="" id="check-all" value="0" aria-label="..." @click="addAndRemoveAllRecordToContainer">
                            </div> -->
                            <div class="divthead-elem wf-200">
                                Name                            
                            </div>
                            <div class="divthead-elem wf-200">
                                Tags                        
                            </div>
                            <div class="divthead-elem wf-200">
                                Sequences                        
                            </div>
                            <div class="divthead-elem wf-200">
                                Emails                      
                            </div>
                            <div class="divthead-elem wf-200">
                                Status
                            </div>
                            <!-- <div class="divthead-elem wf-100">
                                Owner
                            </div> -->
                            <div class="divthead-elem wf-200">
                                Last Contacted
                            </div>
                        </div>
                    </div>
                    <div class="divtbody  fit-divt-content">
                        <div class="divtbody-row" v-for="(record, i) in records.data" :key="record.account_id" :class="[(active_row.id == record.account_id)?'expended':'']">
                            <!-- <div class="divtbody-elem  wf-45 text-center">
                                <div class="form-check">
                                    <input :id="'record-'+record.account_id" class="form-check-input me-1" type="checkbox" :value="record.account_id" @click="addAndRemoveRecordToContainer(record.account_id)">
                                </div>
                            </div> -->
                            <div class="divtbody-elem wf-200">
                                <router-link :to="'/outreach-accounts-details/' + record.account_id" class=""><b>{{ record.name }} </b></router-link><br>
                                <small v-if="record.prospects.length > 0"> {{ record.prospects.length }} Prospects</small>                                
                            </div>
                            <div class="divtbody-elem wf-200">
                                {{ record.tags }}
                            </div>                        
                            <div class="divtbody-elem  wf-200">
                                <span class="bg-primary text-white px-2 py-1 rounded" v-if="record.sequences_active.length > 0">{{ record.sequences_active.length }} Active</span>
                                <span class="bg-secondary text-white px-2 py-1 rounded" v-if="record.sequences_in_active.length > 0">{{ record.sequences_in_active.length }} Inactive</span>                                                            
                            </div>
                            <div class="divtbody-elem  wf-200">

                            </div>
                            <div class="divtbody-elem  wf-200">
                                <span v-if="record.tasks.length > 0" v-title="record.tasks.length + ' task(s)'"><i class="bi bi-bookmark-check"></i></span>
                                                                
                            </div>
                            <!-- <div class="divtbody-elem  wf-100">
                                {{ record.owner }}
                            </div> -->
                            <div class="divtbody-elem  wf-200">
                                {{ record.touchedAt | convertInDayMonth  }}
                            </div>
                        </div>
                    </div>
                    <div class="divtfoot">
                        <div class="text-center py-1">
                            <span class="form-inline d-inline-flex mr-3">
                                <label class="form-control  pr-0 border-none"> Show : </label>
                                <select class="form-control" v-model="form.recordPerPage" @change="getOutreachAccountLists(1)">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <!-- <option value="all">All</option> -->
                                </select>
                            </span>
                            <pagination :limit="5" :data="records" @pagination-change-page="getOutreachAccountLists"></pagination>
                        </div>                    
                    </div>
                </div>
            </div>
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
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import { ToggleButton } from 'vue-js-toggle-button';
import downloadExcel from "vue-json-excel";
import 'vue-select/dist/vue-select.css';
export default {
    components: { DateRangePicker, ToggleButton},
    data() {
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            active_row:{
                id:'',
                type:''
            },
            form: new Form({
                dateRange:{},
                dropdown: '',
                filter:'',
                filterConditionsArray : [],
                filterOption:'is',
                filterText:'',
                filterType:'',
                outreach:1,
                page:1,
                recordPerPage:10,
                sharing : 'private',
                sortBy:'desc',
                sortType:'touchedAt',
                textSearch:'',
                viewName : '',
            }),
            allProspects:{},
            disableFilterItems : false,
            filter : false,
            filterBtn : true,
            filterDateRange : false,
            filterDropdown : false,
            filter_expand:true,
            filterInput : false,
            filterItems : [],
            filterItemsIds : [],
            filterItemsAll : [],
            filterTextReadonly : false,
            recordContainer:[],
            records : {},
            selectedOptions : [],
            selectedOptionsId : [],
            selects : [],
            showSaveView : true,
            showView : false, //control appearance of view controls
            totalNumberOfRecords : 0,
            totalRecords : 0,
            view:'',
            views : [],
        }
    },
    methods: {
        getOutreachAccountLists(pno){
            this.form.page = pno
            this.$Progress.start();
            this.form.post('/api/get-outreach-account-lists').then((response) => {
                this.records = response.data.results;
                this.totalRecords = response.data.page.total;                
                this.$Progress.finish();
                let newRecords = response.data.results.data;
                for(const key in newRecords){
                    this.allProspects[newRecords[key]['id']] = newRecords[key];
                }                
                this.totalNumberOfRecords = response.data.page.total;
                this.loader = false;
            })
        },
        showViewPopup() {
            $('#addView').modal('show');
        },
        refreshAll() {
            this.getOutreachAccountLists(1);
        },
        getFilterData(){
            this.totalNumberOfRecords = '-';    
            this.getOutreachAccountLists(1);
        },
        showFilterDetails(filter, index){
            console.log(filter)
            this.disableFilterItems = true
            if(filter.type == 'textbox'){
                this.form.filter = filter.condition
                this.form.filterOption = filter.formula
                this.form.filterText = filter.textCondition
                this.filter = true
                this.filterInput = true
                this.filterDropdown = false
                this.filterDateRange = false
                var accountFilter = this.filterItemsAll.filter(function(e){
                    if(e.filter_key == filter.condition){
                        return e
                    }
                })
                this.filterOptions = accountFilter[0].filter_option.split(',')
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
                this.form.dateRange.startDate = filter.sdate
                this.form.dateRange.endDate = filter.edate
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
        showFilter(){
            this.filterItems = []
            this.filterTextReadonly = false
            this.filterBtn = true
            this.filter = !this.filter
            axios.get('/api/get-all-filter-for-accounts').then((response) => {
                this.filterItems = response.data.items
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
        removeFilter(index){
            this.form.filterConditionsArray.splice(index, 1)
            this.getOutreachAccountLists(1);
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
                        this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filterConditionsArray[i]["conditionText"] + ' '+ startDate.substring(0,10)+ ' to ' + endDate.substring(0,10)       
                    }
                    this.form.filterText = ''
                    this.form.filter = ''
                    this.form.dropdown = ''
                    this.filter = false
                    this.filterInput = false
                    this.filterDropdown = false
                    this.filterDateRange = false
                    this.filterBtn = true
                    this.disableFilterItems = false
                    this.getOutreachAccountLists(1)
                }
            }
        },
        createFilter(){   
            var sdate = null
            var edate = null        
            if(this.form.filter.filter_type == 'textbox'){
                var textCondition = this.form.filterText  
                var textConditionLabel =this.form.filter.filter +' '+ this.form.filterOption +' '+ this.form.filterText
                var api = '';
            }
            if(this.form.filter.filter_type == 'calendar'){
                    sdate = this.form.dateRange.startDate
                    edate = this.form.dateRange.endDate
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
                'sdate' : sdate,
                'edate' : edate
            }                    
            this.filterItemsIds[this.filterItemsIds.length] = this.form.filter.id
            this.form.filterText = ''
            this.form.filter = ''
            this.form.dropdown = ''
            this.filter = false
            this.filterInput = false
            this.filterDropdown = false
            this.filterDateRange = false
            this.filter = false
            this.getOutreachAccountLists(1)
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
        getSelectedOptions(){            
            // var id = this.form.dropdown
            // var data = this.selects.filter(function(e){
            //     return e.oid == id
            // })
            // if(this.selectedOptions.indexOf(data[0].stage) == -1){
            //     this.selectedOptions.push(data[0].stage)
            //     this.selectedOptionsId.push(data[0].oid)
            // }
        },
        reset () {
            this.item = {}
        },
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        addAndRemoveAllRecordToContainer(){
            
        },
        updateSorting(by) {
            this.form.sortBy = by;
            this.getOutreachAccountLists(1);
        },
        getallview(){
            axios.get('/api/get-all-views-for-accounts').then((response) => {
                this.views = response.data.results;
            });
        },
        saveView(){
            if(this.form.viewName == ''){
                this.$toasted.show("Please enter name!!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    duration : 2000
                });
            } else {
                this.form.post('/api/save-view-accounts').then((response) => {
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
        removeview(){
            this.showSaveView = true
            this.view = ''
            this.form.sortType = 'touchedAt';
            this.form.sortBy = 'asc';
            this.form.dateRange = {};
            this.form.recordPerPage = 20;
            this.form.outreach = 1;
            this.form.page = 1;
            this.form.textSearch = '';
            this.form.filter = '';
            this.form.filterType = '';
            this.form.dropdown = ''
            this.form.filterOption = 'is';
            this.form.filterText = '';
            this.form.filterConditionsArray = [];
            this.form.viewName = '';
            this.getOutreachAccountLists(1)
        },
        selectView(){
            this.filter = false
            if(this.view == null){
                this.form.sortType = 'touchedAt';
                this.form.sortBy = 'asc';
                this.form.dateRange = {};
                this.form.recordPerPage = 20;
                this.form.outreach = 1;
                this.form.page = 1;
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
                this.showSaveView = false
                this.showView = false
                var obj = JSON.parse(currentView[0].view_data);
                this.form.sortType = obj.sortType
                this.form.sortBy = obj.sortBy
                this.form.dateRange = {}
                this.form.recordPerPage = obj.recordPerPage
                this.form.outreach = obj.outreach
                this.form.page = 1
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
            this.filterItemsIds = []
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
            this.getOutreachAccountLists(1)
        },
        makeEmpty(){
            if(this.form.filterOption == 'is empty' || this.form.filterOption == 'is not empty'){
                this.form.filterText = ''
                this.filterTextReadonly = true
            }else{
                this.filterTextReadonly = false
            }
        }
    },
    mounted() {
        this.getOutreachAccountLists()
        this.getallview()
        axios.get('/api/get-all-filter-for-accounts').then((response) => {
            this.filterItems = response.data.items
            this.filterItemsAll = response.data.items            
        });
    }
}
</script>
<style>

</style>