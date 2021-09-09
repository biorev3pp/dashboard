<template>
    <div>
        <div>
            <div class="table-responsive border-bottom">
               <dataset-count :total="totalNumberOfRecords" :activeDataset="form.dataset"></dataset-count>
            </div>
            <div class="filterbox">
                <div class="row mx-0 mb-2">
                    <div class="col-md-3 pl-0">
                        <div class="input-group in-search-group">
                            <input type="text" class="form-control" v-model="form.textSearch" placeholder="Search by name, email or company">
                            <div class="input-group-append">
                                <button class="btn" type="button" @click="getFilterData">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12 p-0">
                        <span v-for="(filter,index) in form.filterConditionsArray" :key="'filter-'+filter.conditionText+'-'+filter.formula+'-'+filter.textCondition" class="filter-btns" v-show="filter_expand">
                            <span v-title="filter.textConditionLabel"  class="text-dark mx-1 pointer-hand" @click="showFilterDetails(filter, index)"> {{ filter.textConditionLabel }}</span>
                            <i class="bi bi-x pr-1  pointer-hand" @click="removeFilter(index)"></i>
                        </span>
                        <span class="filter-btns" v-show="(filter == true) && (typeof form.filter == 'object')">
                            <span  class="text-dark mx-1 pointer-hand"> {{ (form.filter)?form.filter.filter:'-' }}</span>
                            <i class="bi bi-x pr-1  pointer-hand"></i>
                        </span>
                        <div class="stage-select-box selection-box" v-show="filter">
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
                                        <br>
                                        <span v-for="filterOption in filterOptions" :key="'select-'+filterOption" :value="filterOption" class="mselect-bs">
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
                                        <br>
                                        <span v-for="filterOption in filterOptions" :key="'select-'+filterOption" :value="filterOption" class="mselect-bs">
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
                                                <span class="badge bg-primary text-white p-2 m-1 pointer-hand" v-for="(option, index) in selectedOptions" :key="'option-'+index" @click="removeSelectedOption(index)">{{ option }} </span> 
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
                            <div class="stage-box-footer text-center border-top p-2 mb-2">
                                <button v-show="filterBtn" v-if="filterInput || filterDropdown || filterDateRange || filterEmail || filterPhone" class="btn btn-primary btn-sm" @click="createFilter">Done</button>
                                <button v-show="!filterBtn" v-if="filterInput || filterDropdown || filterDateRange || filterEmail || filterPhone" class="btn btn-primary btn-sm" @click="updateFilter">Update</button>
                            </div> 
                        </div>
                        <div class="position-relative d-inline-block">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-dark btn-sm text-capitalize" @click="showFilter()"><i class="bi bi-plus"></i> Add filter</button>
                                <button type="button" class="btn btn-secondary btn-sm" @click="removeAllFilter">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
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
            <div class="divtable border-top">
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-45 text-center">
                            <input type="checkbox" name="" id="check-all" value="0" aria-label="...">
                        </div>
                        <div class="divthead-elem wf-125">
                            Dataset                        
                        </div>
                        <div class="divthead-elem mwf-200">
                            Name                            
                        </div>
                        <div class="divthead-elem wf-175">
                            Stage                        
                        </div>
                        <div class="divthead-elem wf-150">
                            Call Stack  
                        </div>
                        <div class="divthead-elem wf-220">
                            Email Stack                   
                        </div>
                        <div class="divthead-elem wf-150">
                            Time Stack
                        </div>
                    </div>
                </div>
                <div class="divtbody  fit-divt-content2">
                    <div class="divtbody-row" v-for="record in records.data" :key="'dsg-'+record.id" :class="[(active_row.id == record.record_id)?'expended':'']">
                        <div class="divtbody-elem  wf-45 text-center">
                            <div class="form-check">
                                <input :id="'record-'+record.id" class="form-check-input me-1" type="checkbox" :value="record.id">
                            </div>
                        </div>
                        <div class="divtbody-elem  wf-125">
                            <dataset-group :a="record.email_delivered" :b="record.email_clicked" :c="record.email_opened" :d="record.mcall_attempts" :e="record.mcall_received" :f="record.wcall_attempts" :g="record.wcall_received" :h="record.stage" :i="record.dataset" :j="record.hcall_attempts" :k="record.hcall_received" ></dataset-group>
                        </div>
                        <div class="divtbody-elem mwf-200">
                            <router-link target="_blank" :to="'prospects/' + record.record_id" class="text-capitalize"><b>{{ record.first_name }} {{ record.last_name }} </b></router-link>
                            <br>
                            <small class="fw-500" v-title="record.title" v-if="record.title">{{ record.title }}  in </small> 
                            <span class="company-sm" v-title="record.company" v-if="record.company">{{ record.company }}</span>
                        </div>
                        <div class="divtbody-elem wf-175">
                            <span v-if="record.stage_name" :class="record.stage_css" v-title="record.stage_name">
                                {{ record.stage_name }}
                            </span>
                            <span class="no-stage" v-else>No Stage</span>
                        </div>
                        <div class="divtbody-elem wf-150">
                            <span class="stack-box call-log">
                                <label for="call">
                                    <i class="bi bi-telephone-fill"></i>
                                </label>
                                <call-log :call="record.mcall_attempts" :rcall="record.mcall_received" :title="record.mobilePhones" :fnumber="record.mnumber" :label="'MP'"></call-log>
                                <call-log :call="record.wcall_attempts" :rcall="record.wcall_received" :title="record.workPhones" :label="'WP'"></call-log>
                                <call-log :call="record.hcall_attempts" :rcall="record.hcall_received" :title="record.homePhones" :label="'HP'"></call-log>
                            </span>
                        </div>
                        <div class="divtbody-elem wf-220">
                            <span class="stack-box email-log">
                                <label for="email">
                                    <i class="bi bi-envelope-fill text-primary"></i>
                                </label>
                                <email-log :te="record.email_delivered" :tc="record.email_clicked" :to="record.email_opened" :tb="record.email_bounced" :tr="record.email_replied" :title="record.emails" :label="'B'"></email-log>
                                <email-log :te="0" :tc="0" :to="0" :tb="0" :tr="0" :title="record.supplemental_email?record.supplemental_email:''" :label="'S'"></email-log>
                            </span>
                        </div>                             
                        <div class="divtbody-elem  wf-150">
                            <span class="stack-box">
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
                            <select class="form-control" v-model="form.recordPerPage" @change="getDatasets">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </span>
                        <pagination :limit="5" :data="records" @pagination-change-page="getDatasets"></pagination>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import DatasetGroup from './Group';
import CallLog from './CallLog';
import EmailLog from './EmailLog';
import DatasetCount from './Counts';
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import { ToggleButton } from 'vue-js-toggle-button';
import 'vue-select/dist/vue-select.css';

export default {
    components:{CallLog, EmailLog, DatasetGroup, DatasetCount, DateRangePicker, ToggleButton},
    data() {
        return {
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
            form: new Form({
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
            }),
            showView : false, //control appearance of view controls
            loader_url: '/img/spinner.gif',
            totalNumberOfRecords:'',
            recordContainer:'',
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
            filter_keyword:''
        }
    },
    filters: {
           },
    computed: {
        datasets() {
            return this.$store.getters.datasets
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
                    return item.filter_key.toLowerCase().indexOf(this.filter_keyword.toLowerCase()) > -1
                })
            }
        },
    },
    methods: {
        removeAllFilter() {
            this.form.filterConditionsArray = [];
            this.filterBtn = true
            this.getDatasets(1);
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
        },
        showFilterOption(fitem){
            this.filter = true
            this.showView = false
            this.form.filterOption = ''
            if(this.form.filter == null){
                this.filterInput = false;
                this.filterDropdown = false;
                this.filterDateRange = false;
            }else{
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
                        this.showFilterDetails(oldData[0], 1)
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
                        this.showFilterDetails(oldData[0], 1)
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
                }else{
                    var query = 'or'
                }               
                var textConditionLabel = this.form.filter.filter + " with " + this.searchfilterEmailMoreOption.join(", " + query +' ')
                oldformula = this.form.filter.filter_option
                this.form.filterOption = this.queryType

            }
            
            if(this.form.filter.filter_type == 'phone'){
                var textCondition = this.searchfilterPhoneMoreOption.join(", ")
                if(this.queryType == 'all'){
                    var query = 'and'
                }else{
                    var query = 'or'
                }
                var textConditionLabel = this.form.filter.filter + " with " + this.searchfilterPhoneMoreOption.join(", " + query +' ')

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
            this.getDatasets(1)
        },        
        showFilterDetails(filter, index){
            
            if(filter.type == 'textbox'){
                this.form.filter = filter.condition                
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
                this.form.filter = filter.condition
                this.form.filterOption = filter.formula
                this.form.dropdown = filter.textCondition
                this.filterOptions = []
                this.filterOptions = filter.oldformula.split(",")
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
                this.filterEmail = false
                this.filterPhone = false
            } else if(filter.type == 'calendar'){
                this.form.filter = filter.condition
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
                
                if(this.form.filterConditionsArray[i]["condition"] == this.form.filter || this.form.filterConditionsArray[i]["condition"] == this.bypassFIlterKey){
                    
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
                        this.form.filterConditionsArray[i]["textCondition"] = this.selectedOptionsId.join(',')        
                        if(this.form.filterOption == 'is empty' || this.form.filterOption == 'is not empty'){
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +' '+ this.form.filterOption
                        } else {
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +' '+ this.form.filterOption +' '+ this.selectedOptions.join(', ')
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
                        }else{
                            this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +  " with " + this.searchfilterEmailMoreOption.join(",  or ")
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
                    this.queryType = ""
                    this.bypassFIlterKey = ''
                    this.getDatasets(1)
                }
            }
        },
        removeFilter(index){
            this.form.filterConditionsArray.splice(index, 1)
            this.filterBtn = true
            this.getDatasets(1);
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
        getDatasets(page) {
            this.loader = true;
            this.$Progress.start();  
            this.form.page = page
            this.form.post('/api/dataset-values-data').then((response) => {                
                this.records = response.data;
                this.totalRecords = response.data.total;                
                this.$Progress.finish();               
                this.totalNumberOfRecords = response.data.total;
                this.loader = false;
            });
        },
        getFilterData(){
            this.form.sortType = 'outreach_touched_at'
            this.form.sortBy = 'asc'
            this.totalNumberOfRecords = '-';    
            this.getDatasets(1);
        },
    },
    beforeMount() {
        if(this.datasets == '') {
           this.$store.dispatch('setDatasets');
        }
    },
    mounted() {
        this.getDatasets(1);
        if(this.filterItems == '') {
           this.$store.dispatch('setDatasetFilter');
        }
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