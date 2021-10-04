<template>
    <div>
        <div>
            <div class="table-responsive border-bottom chart-dash">
                <div class="outer-synop cursor-pointer" style="left:0">
                    <h4 class="number">{{ totalProspects | freeNumber }} </h4>
                    <p class="text-success">Total</p>
                </div>
                <div class="synops" v-if="loader">
                    <div class="inner-synop cursor-pointer">
                        <h4  class="number" >
                            <img :src="loader_url" alt="Loading...">
                        </h4>
                        <p>Loading...</p>
                    </div>
                </div>
               <div class="synops" v-else>          
                    <div class="inner-synop cursor-pointer" v-for="(detail, i) in synopValues"  :key="'row-'+i"   @click="dataPointSelectionHandlerTop(detail.value)">
                        <h4  class="number" >{{ detail.count | freeNumber }} 
                            <small>({{ detail.percentage }})</small> 
                        </h4>
                        <p>{{ detail.value }}</p>
                    </div>
               </div>
                <div class="outer-synop cursor-pointer text-danger" style="right:0">
                    <h4 class="number">
                        <img :src="loader_url" alt="Loading..." v-if="loader">
                        <i class="bi bi-gear-wide-connected"></i>    
                    </h4><p class=" text-danger">Settings</p>
                </div>
            </div>
            <div class="filterbox p-2">
                <div class="row m-0">
                    <div class="col-md-10 col-12 p-0 filter-btns-holder">
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
                    <div class="col-md-2 col-12 p-0 text-right">
                        <a href="/datasets" class="btn btn-primary theme-btn icon-btn-left" target="_blank">
                            <i class="bi bi-list-ul"></i> View
                        </a>
                    </div>
                </div>
            </div>
            <div class="graphics-div border-top">
                <div class="chart-dmode">
                    <button v-if="this.form.filterConditionsArray.length > 0" class="btn btn-primary" @click="moveBack">Back</button>
                    <ul v-for="list in form.historyValue" :key="'list' + list">
                        <li > {{ list }} </li>
                    </ul>
                    <label for="" class="text-uppercase fw-500 mb-0">Display Mode</label>
                    <v-select label="title" :options="graphFilterItems" v-model="form.primary_filter" @input="getDatasetGraphFilter"></v-select>
                </div>
                <div class="chart-box">
                    <apexchart v-if="maplabelsS.length > 0" width="90%" height="94%" type="pie" :options="chartOptionsS" :series="seriesS" @dataPointSelection="dataPointSelectionHandler" @dataPointMouseEnter="dataPointMouseEnterHandler" @dataPointMouseLeave="dataPointMouseLeaveHandler"></apexchart>
                </div>
            </div>
        </div>
        <div class="overlay-loader" v-show="loader">
            <b-spinner style="width: 3rem; height: 3rem;" label="Loading.."></b-spinner>
        </div>
    </div>
</template>
<script>

import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import 'vue-select/dist/vue-select.css';

export default {
    components:{DateRangePicker},
    data() {
        return {
            stages : [],
            bypassFIlterKey : '',
            filter_expand:true,
            loader:false,
            chartview:false,
            step:0,
            records:{},
            totalRecords : 0,
            active_row:'',
            searchfilterEmailMoreOption:[],
            searchfilterPhoneMoreOption:[],
            queryType : "",
            leftpos:'0px',
            toppos:'0px',
            form: new Form({
                sortType:'outreach_touched_at',
                sortBy:'desc',
                recordPerPage:20,
                pageno:1,
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
                historyKey:[],
                historyValue:[],
                primary_filter:{
                            id: 7,
                            filter_option: "is,is not,starts with,is empty,is not empty,contains",
                            filter_type: "textbox",
                            api: null,
                            title: "Country",
                            value: "country",
                            options: null
                        }
            }),
            allValues:[],
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
            filter_keyword:'',
            paiDataS : [],
            maplabelsS : [],
            totalProspects:'',
            graphFilterItems:[],
            sortBy:'count'
        }
    },
    filters: {
           },
    computed: {
        synopValues() {
            return this.allValues.sort((p1,p2) => {
                let modifier = -1;
                if(p1[this.sortBy] < p2[this.sortBy]) return -1 * modifier; if(p1[this.sortBy] > p2[this.sortBy]) return 1 * modifier;
                return 0;
            });
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
        seriesS(){
            if(this.paiDataS.length > 0){
                return this.paiDataS                
            }else{
                return []
            }
        },
        chartOptionsS(){
            if(this.maplabelsS.length > 0){
                return  {
                    legend: {
                        show: false,
                        position: 'bottom',
                        horizontalAlign: 'center', 
                        floating: false,
                        fontSize: '10px',
                        fontFamily: 'inherit',
                        fontWeight: 300,
                    },
                    colors : ['#3490dc', '#6574cd' ,'#9561e2', '#f66d9b', '#e3342f', '#f6993f', '#ffed4a' ,'#38c172' ,'#4dc0b5' ,'#6cb2eb'],
                    stroke: {
                        show: false,
                        curve: 'stepline',
                        lineCap: 'butt',
                        width: 1,
                        dashArray: 0,      
                    },
                    chart: {
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        },
                        fontFamily: 'inherit',
                    },
                    labels: this.maplabelsS,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                // width: 200,
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                }
            }else{
                return  []
            }
        },
    },
    methods: {
        removeAllFilter() {
            this.form.filterConditionsArray = [];
            this.filterBtn = true
            this.showView = false
            this.filter = false
            this.getDatasetGraphFilter()
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
        dataPointSelectionHandlerTop(value) {
            var label = value
            var value = this.form.primary_filter.value
            var data = this.form.filterConditionsArray.filter(function(e){
                return e.condition != value
            })
            
            this.form.filterConditionsArray = data
            var oid = "";
            var id = "";
            var formula = '';
            var type = ""
            if(this.form.primary_filter.value == "stage"){
                //oid = response.data
                oid = this.stages.filter( (element, index) => {
                    return element.stage == label
                })
            }
            var message = "";
            if(label == 'Empty'){
                if(oid){
                    id = oid[0]["oid"].toString()
                    formula = "is"
                    message = this.form.primary_filter.title  + " is " + label
                    type = "dropdown"
                }else{
                    id = ""
                    formula = "is empty"
                    message = this.form.primary_filter.title  + " is empty"
                    type = "textbox"
                }
                this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                    api: this.form.primary_filter.api,
                    condition: this.form.primary_filter.value,
                    conditionText: this.form.primary_filter.title,
                    formula: formula,
                    oldformula: this.form.primary_filter.filter_option,
                    textCondition: id,
                    textConditionLabel: message,
                    type: type,
                }
            } else if(label == 'Not Empty'){
                if(oid){
                    id = oid[0]["oid"].toString()
                    formula = "is"
                    message = this.form.primary_filter.title  + " is " + label
                    type = "dropdown"
                }else{
                    id = ""
                    formula = "is not empty"
                    message = this.form.primary_filter.title  + " is empty"
                    type = "textbox"
                }
                this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                    api: this.form.primary_filter.api,
                    condition: this.form.primary_filter.value,
                    conditionText: this.form.primary_filter.title,
                    formula: formula,
                    oldformula: this.form.primary_filter.filter_option,
                    textCondition: id,
                    textConditionLabel: message,
                    type: type,
                }
            }else{
                if(oid){
                    id = oid[0]["oid"].toString()
                    formula = "is"
                    message = this.form.primary_filter.title  + " is " + label
                    type = "dropdown"
                }else{
                    id = label
                    formula = "is"
                    message = this.form.primary_filter.title  + " is " + label
                    type = "textbox"
                }
                this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                    api: this.form.primary_filter.api,
                    condition: this.form.primary_filter.value,
                    conditionText: this.form.primary_filter.title,
                    formula: formula,
                    oldformula: this.form.primary_filter.filter_option,
                    textCondition: id,
                    textConditionLabel: message,
                    type: type,
                }
            }
            this.form.primary_filter = ''
            this.getDatasetGraphFilter()
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
            this.getDatasetGraphFilter()
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
                        this.selectedOptions.push(newStage[0].name)
                    }
                }
                this.filterEmail = false
                this.filterPhone = false
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
                    this.queryType = ""
                    this.bypassFIlterKey = ''
                    this.getDatasetGraphFilter()
                }
            }
        },
        removeFilter(index){
            this.form.filterConditionsArray.splice(index, 1)
            this.filterBtn = true
            this.showView = false
            this.filter = false
            this.getDatasetGraphFilter();
        },        
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
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
        dataPointMouseLeaveHandler(e, chartContext, config){
            this.selectedSlide = ''
        },
        dataPointMouseEnterHandler(e, chartContext, config){
            this.selectedSlide = config.dataPointIndex
        },
        dataPointSelectionHandler(e, chartContext, config) {    
            var name = this.maplabelsS[config.dataPointIndex]
            var label = name.substring(name.lastIndexOf(":")+2)
            if(label == "All"){
                return false
            }
            //form.primary_filter
            var value = this.form.primary_filter.value
            var data = this.form.filterConditionsArray.filter(function(e){
                return e.condition != value
            })
            
            this.form.filterConditionsArray = data
            var oid = "";
            var id = "";
            var formula = '';
            var type = ""
            if(this.form.primary_filter.value == "stage"){
                //oid = response.data
                oid = this.stages.filter( (element, index) => {
                    return element.stage == label
                })
            }
            console.log(oid)
            var message = "";
            if(label == 'Empty'){
                if(oid){
                    id = oid[0]["oid"].toString()
                    formula = "is"
                    message = this.form.primary_filter.title  + " is " + label
                    type = "dropdown"
                }else{
                    id = ""
                    formula = "is empty"
                    message = this.form.primary_filter.title  + " is empty"
                    type = "textbox"
                }
                this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                    api: this.form.primary_filter.api,
                    condition: this.form.primary_filter.value,
                    conditionText: this.form.primary_filter.title,
                    formula: formula,
                    oldformula: this.form.primary_filter.filter_option,
                    textCondition: id,
                    textConditionLabel: message,
                    type: type,
                }
            } else if(label == 'Not Empty'){
                if(oid){
                    id = oid[0]["oid"].toString()
                    formula = "is"
                    message = this.form.primary_filter.title  + " is " + label
                    type = "dropdown"
                }else{
                    id = ""
                    formula = "is not empty"
                    message = this.form.primary_filter.title  + " is empty"
                    type = "textbox"
                }
                this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                    api: this.form.primary_filter.api,
                    condition: this.form.primary_filter.value,
                    conditionText: this.form.primary_filter.title,
                    formula: formula,
                    oldformula: this.form.primary_filter.filter_option,
                    textCondition: id,
                    textConditionLabel: message,
                    type: type,
                }
            }else{
                if(oid){
                    id = oid[0]["oid"].toString()
                    formula = "is"
                    message = this.form.primary_filter.title  + " is " + label
                    type = "dropdown"
                }else{
                    id = label
                    formula = "is"
                    message = this.form.primary_filter.title  + " is " + label
                    type = "textbox"
                }
                this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                    api: this.form.primary_filter.api,
                    condition: this.form.primary_filter.value,
                    conditionText: this.form.primary_filter.title,
                    formula: formula,
                    oldformula: this.form.primary_filter.filter_option,
                    textCondition: id,
                    textConditionLabel: message,
                    type: type,
                }
            }
            this.form.primary_filter = ''
            this.getDatasetGraphFilter()
            

        },
        getGraphSearchCriteria(){
            axios.get("/api/get-graph-search-criteria").then((response) => {
                this.graphFilterItems = response.data
            })
        },
        moveBack(){
            var lele = this.form.filterConditionsArray[this.form.filterConditionsArray.length - 1]
            
            axios.get("/api/get-graph-search-criteria-record?value=" + lele.condition).then( (response) => {
                this.form.primary_filter = response.data.results
                this.form.filterConditionsArray.pop()
                this.getDatasetGraphFilter()
            })
        },
        getDatasetGraphFilter(){
            this.loader = true
            this.form.post("/api/get-dataset-graph-filter").then((response) => {
                this.paiDataS = response.data.paiDataS
                this.maplabelsS = response.data.maplabelsS
                this.allValues = response.data.allValues
                this.graphFilter = response.data.graphFilter
                this.form.primary_filter = response.data.graphNext
                this.totalProspects = response.data.totalContacts
                this.graphFilterItems = response.data.graphFilterItems
                this.form.historyKey  = response.data.historyKey
                this.form.historyValue  = response.data.historyValue
                this.loader = false
            })
        },
    },
    created() {
        this.getDatasetGraphFilter()
        this.getGraphSearchCriteria()
        if(this.filterItems == '') {
           this.$store.dispatch('setDatasetFilter');
        }
    }
}
</script>