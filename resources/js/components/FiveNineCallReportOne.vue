<template>
    <div>
        <div class="top-form p-2">
            <div class="row m-0">
                <div class="col-md-3 col-12 pl-0">
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
                <div class="col-md-3 col-12 p-0">
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="getCallReport">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                        <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z"/>
                        </svg>
                        Get Data 
                    </button>
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="reset">
                         Reset
                    </button>
                    <span class=" text-right" v-if="loader">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </span>
                    <span class=" full-width text-center" v-if="loader2">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </span>
                </div>
                <div class="col-md-6 col-3">
                    <div class="row">
                        <div class="col-md-3 col-6 pl-0">
                            <select class="form-control" v-model="disposition" @change="filter">
                                <option value="">Select Disposition</option>
                                <option v-for="dispo in dispositions" :key="dispo" :value="dispo">{{ dispo }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-6 pl-0">
                            <select class="form-control" v-model="campaign" @change="filter">
                                <option value="">Select Campaign</option>
                                <option v-for="camp in campaigns" :key="camp" :value="camp">{{ camp }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-6 pl-0">
                            <select class="form-control" v-model="callAttempt" @change="filter">
                                <option value="">Select Call Attempts</option>
                                <option value="-">-</option>
                                <option v-for="dial in callAttempts" :key="dial" :value="dial"  v-if="dial > 0">{{ dial }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-6 pl-0">
                            <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="clear">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mapping-div">
            <div class="p-2">
                <p v-if="recordsCount == 0" class="text-danger">Please select date</p>
                <table class="table table-bordered table-striped full-table" v-else>
                    <thead>
                        <tr>
                            <th>Sno</th>
                            <th>Record Id
                                <i class="bi bi-caret-up pointer" @click="sorting='record_id',sortingType='asc'"></i>
                                <i class="bi bi-caret-down pointer" @click="sorting='record_id',sortingType='desc'"></i>
                            </th>
                            <th>Customer Name
                                <i class="bi bi-caret-up pointer" @click="sorting='customer_name',sortingType='asc'"></i>
                                <i class="bi bi-caret-down pointer" @click="sorting='customer_name',sortingType='desc'"></i>
                            </th>
                            <th>Timestamp</th>
                            <th>Call Attempt
                                <i class="bi bi-caret-up pointer" @click="sorting='dial_attempts',sortingType='asc'"></i>
                                <i class="bi bi-caret-down pointer" @click="sorting='dial_attempts',sortingType='desc'"></i>
                            </th>
                            <th>DNIS</th>
                            <th>Dispositions
                                <i class="bi bi-caret-up pointer" @click="sorting='disposition',sortingType='asc'"></i>
                                <i class="bi bi-caret-down pointer" @click="sorting='disposition',sortingType='desc'"></i>
                            </th>
                            <th>Agent Name
                                <i class="bi bi-caret-up pointer" @click="sorting='agent_name',sortingType='asc'"></i>
                                <i class="bi bi-caret-down pointer" @click="sorting='agent_name',sortingType='desc'"></i>
                            </th>
                            <th>Call Type
                                <i class="bi bi-caret-up pointer" @click="sorting='call_type',sortingType='asc'"></i>
                                <i class="bi bi-caret-down pointer" @click="sorting='call_type',sortingType='desc'"></i>
                            </th>
                            <th>List Name
                                <i class="bi bi-caret-up pointer" @click="sorting='list_name',sortingType='asc'"></i>
                                <i class="bi bi-caret-down pointer" @click="sorting='list_name',sortingType='desc'"></i>
                            </th>
                            <th>Campaign Name
                                <i class="bi bi-caret-up pointer" @click="sorting='campaign',sortingType='asc'"></i>
                                <i class="bi bi-caret-down pointer" @click="sorting='campaign',sortingType='desc'"></i>
                            </th>
                            <th>Home Phones</th>
                            <th>Work Phones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr  v-for="(call, index) in orderedCalls" :key="'record'+index" :class="'record record-'+Math.ceil((Number(index)+1)/per_page)" v-show="Math.ceil((Number(index)+1)/per_page) == 1">
                            <td>{{ index + 1 }}</td>
                            <td> 
                                <span v-if="call.record_id.length == 0 ">-</span><span v-else> {{ call.record_id }} </span>
                            </td>
                            <td> 
                                <span v-if="call.customer_name.length == 0 ">-</span><span v-else> {{ call.customer_name }} </span>
                            </td>
                            <td> {{ call.timestamp }} </td>
                            <td> 
                                <span v-if="call.dial_attempts.length == 0 ">-</span><span v-else> {{ call.dial_attempts }} </span>
                            </td>
                            <td> {{ call.dnis }} </td>
                            <td> 
                                <span v-if="call.disposition.length == 0 ">-</span><span v-else> {{ call.disposition }} </span>
                            </td>
                            <td> 
                                <span v-if="call.agent_name.length == 0 ">-</span><span v-else> {{ call.agent_name }} </span>
                            </td>
                            <td> {{ call.call_type }} </td>
                            <td> 
                                <span v-if="call.list_name.length == 0 || call.list_name == '[None]'">-</span><span v-else> {{ call.list_name }} </span>
                            </td>
                            <td> 
                                <span v-if="call.campaign.length == 0 || call.campaign == '[None]'">-</span><span v-else> {{ call.campaign }} </span>
                            </td>
                            <td> <span v-if="call.number2.length == 0 ">-</span><span v-else>{{ call.number2 }} </span></td>
                            <td> <span v-if="call.number3.length == 0 ">-</span><span v-else>{{ call.number3 }} </span></td>
                        </tr>
                    </tbody>
                </table>
                <div class="class" v-if="recordsCount > 0">
                    <div class="text-center py-1">
                        <span class="form-inline d-inline-flex mr-3">
                            <label class="form-control  pr-0 border-none"> Show : </label>
                            <select class="form-control" v-model="per_page" @change="setPerPage()">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </span>
                        <ul class="pagination" id="paginator">
                            <li v-for="p in paginationArray" :key="'page'+p" class="page-item pagination-page-nav">
                                <a class="page-link page-link-active" v-if="p == pno"> {{p}} </a>
                                <a class="page-link" href="javascript:;" @click="pagination(p)" v-else> {{p}} </a>
                            </li>
                        </ul>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker';
export default {
    components: { DateRangePicker },
    data() {
        return {
            callAttempt : '',
            callAttempts : [],
            allCalls : [],
            calls : [],
            campaign : '',
            campaigns : [],
            disposition : '',
            dispositions : [],
            filteredRecords : 0,
            header : [],
            loader : false,
            loader2 : false,
            pages : 0,
            paginationArray : {},
            per_page : 10,
            recordsCount : 0,
            pno : 1,
            sorting : 'record_id',
            sortingType : 'asc',
            form : new Form({
                agent : 0,
                dateRange : {},
                email : '',
                id : '',
                agent_id : [],
            }),
        }
    },
    computed: {
        orderedCalls: function () {            
            return _.orderBy(this.calls, this.sorting, this.sortingType)
        },
    },
    filters: {
        capitalize: function (str) {
            return str.charAt(0).toUpperCase() + str.slice(1)
        },
        titleFormat: function (str) {
            let nstr = str.charAt(0).toUpperCase() + str.slice(1);
            return nstr.replace('_', ' ');
        },
        phoneFormatted: function (str) {
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
        },
    },
    methods: {
        clear(){
            this.sorting = 'record_id',
            this.sortingType = 'asc',
            this.disposition = ''
            this.campaign = ''
            this.callAttempt =  ''
            this.calls = this.allCalls
            this.filteredRecords = this.recordsCount = this.calls.length
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1) 
        },
        setPerPage(){
            this.pages = Math.ceil(this.recordsCount/this.per_page)            
            this.pagination(1)
        },
        pagination(current_page){
            this.pno = current_page
            $(".record").css("display", "none")
            $(".record-"+current_page).css("display", "table-row")
            var start = 1;
            var end = this.pages
            if( current_page <= 5){
                if(this.pages >= 11){
                    start = 1
                    end = 11
                } else {
                    start = 1
                    end = this.pages
                }
            } else { 
                if(this.pages >= 11){
                    start = current_page -5
                    end = start + 11
                    if(end > this.pages){
                        end = this.pages
                        start = end - 11
                    }
                }
            }
            this.paginationArray = {}
            for(var i = start; i <= end; i++){
                this.paginationArray[i] = i
            }
        },
        filter(){
            var recordList = this.allCalls
            if(this.disposition){
                var newRecordList = recordList.filter((element) => element.disposition && element.disposition == this.disposition)
                recordList = newRecordList
            }
            if(this.campaign){
                var newRecordList = recordList.filter((element) => element.campaign && element.campaign == this.campaign)
                recordList = newRecordList
            }
            if(this.callAttempt){
                var newRecordList = recordList.filter((element) => element.dial_attempts && element.dial_attempts == this.callAttempt)
                recordList = newRecordList
            }
            this.recordList = []
            this.calls = recordList
            this.filteredRecords = this.recordsCount = this.calls.length
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1) 
        },
        reset(){
            this.calls = []
            this.form.dateRange = {}
            this.form.id = ''
            this.recordsCount = 0
        },
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        getCallReport(){
            this.$Progress.start()
            this.clear()
            this.calls = []
            this.recordsCount = 0
            this.loader = true
            this.form.post('/api/get-call-reports-01').then((response) => {
                if(response.data.hasOwnProperty("id")){
                    var id = response.data.id;
                    this.form.id = id
                    axios.get('/api/get-five-nine-report-response/' + id).then((responseR) => {
                        if(responseR.data.result == true){
                            this.form.post('/api/get-five-nine-call-log-report-result-01').then((responseReport) => {
                                this.loader = false
                                if(responseReport.data.results == false){
                                    this.$Progress.finish()
                                    Vue.$toast.warning('No records found !!');
                                } else {
                                    this.calls = responseReport.data.records      
                                    this.allCalls = responseReport.data.records      
                                    this.dispositions = responseReport.data.disposition      
                                    this.campaigns = responseReport.data.campaign
                                    this.callAttempts = responseReport.data.dial_attempts
                                    this.recordsCount = this.calls.length
                                    this.pages = Math.ceil(this.recordsCount/this.per_page)
                                    this.loader = false
                                    this.pagination(1)
                                    this.$Progress.finish()
                                }
                            });
                        }  
                    })
                } else {
                    Vue.$toast.warning('Please try after some time !!');
                }
            })
        },
    },
    mounted() {
    }
}
</script>
<style>
.full-width{
    width : 100%;
}
.full-table{
        overflow-x: scroll;
}
</style>