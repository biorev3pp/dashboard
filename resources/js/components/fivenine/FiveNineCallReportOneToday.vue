<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-4 col-12 pl-0">
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
                <div class="col-md-4 col-12 p-0">
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="getCallReport">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                        <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z"/>
                        </svg>
                        Get Data 
                    </button>
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="reset">
                         Reset
                    </button>
                </div>
                <div class="col-md-4 col-12 p-0">
                    <div class="form-group">
                        <select class="form-control" v-model="dipo" >
                            <option v-for="(disposition, index) in dispositions" :key="'dispo-' + index" :value="disposition">{{ disposition }}</option>
                        </select>
                    </div>
                </div>
            </div>


        </div>
        <div class="mapping-div">
            <div class=" full-width text-center" v-if="loader">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
            <div>
                <table class="table table-bordered table-striped full-table">
                    <thead>
                        <tr>
                            <th>Record Id</th>
                            <th>Customer Name</th>
                            <th>Timestamp</th>
                            <th>Call Attempt</th>
                            <th>DNIS</th>
                            <th>Dispositions</th>
                            <th>Agent Name</th>
                            <th>Call Type</th>
                            <th>List Name</th>
                            <th>Campaign Name</th>
                            <th>Home Phones</th>
                            <th>Work Phones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr  v-for="(call, index) in calls" :key="'record'+index" :class="'record-'+Math.ceil((Number(index)+1)/10)" v-show="Math.ceil((Number(index)+1)/10) == 1">
                            <td> {{ call.record_id }} </td>
                            <td> {{ call.customer_name }} </td>
                            <td> {{ call.timestamp }} </td>
                            <td> {{ call.dial_attempts }} </td>
                            <td> {{ call.dnis }} </td>
                            <td> {{ call.disposition }} </td>
                            <td> 
                                <span v-if="call.agent_name.length == 0 ">-</span><span v-else>{{ call.agent_name }} </span></td>
                            <td> {{ call.call_type }} </td>
                            <td> {{ call.list_name }} </td>
                            <td> {{ call.campaign }} </td>
                            <td> <span v-if="call.number2.length == 0 ">-</span><span v-else>{{ call.number2 }} </span></td>
                            <td> <span v-if="call.number3.length == 0 ">-</span><span v-else>{{ call.number3 }} </span></td>
                            <!-- <td style="min-width:200px;" v-for="(rec, rindex) in record.values.data" :key="'record-data'+rindex">    
                                <span v-if="rec == '[None]' || rec.length == 0" >--</span>                        
                                <span v-else>{{ rec }}</span>
                            </td> -->
                        </tr>
                    </tbody>
                </table>
                <div class=" full-width text-center" v-if="loader2">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div class="row m-0" v-if="showPaginatioin">
                    <div class="col-md-3 p-0 text-center">
                        
                    </div>
                    <div class="col-md-6 text-right">
                        <paginationSimple v-model="page" :records="totalRecords" @paginate="showPaginationRecord" :per-page="10"/>
                    </div>
                    <div class="col-md-3 text-right">
                        
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
            sorting : 'record_id',
            sortingType : 'asc',
            allCalls : [],
            dipo:[],
            dispositions : [],
            header : [],
            showPaginatioin :  false,
            loader : false,
            loader2 : false,
            page: 1,
            calls : [],
            totalRecords : 0,
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
        orderedContacts: function () {            
            return _.orderBy(this.calls, this.sorting, this.sortingType)
        },
        campaignList: function(){
            return _.orderBy(this.camList)
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
        reset(){
            this.page = 1
            this.calls = []
            this.form.dateRange = {}
            this.form.id = ''
            this.totalRecords = 0
        },
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        getCallReport(){
            this.$Progress.start()
            this.nuberOfPage = 0
            this.loader = true
            this.calls = []
            this.page = 1
            this.showPaginatioin = false
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
                                    //this.header = responseReport.data.header.values.data 
                                    this.calls = responseReport.data.records      
                                    this.allCalls = responseReport.data.records      
                                    this.dispositions = responseReport.data.disposition      
                                    this.totalRecords = this.calls.length
                                    this.nuberOfPage = Math.ceil(Number(this.totalRecords)/10);
                                    this.loader = false
                                    if(this.nuberOfPage > 0){
                                        this.showPaginatioin = true
                                    }
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
        showPaginationRecord: function(page) {
            this.loader2 = true
            for (let index = 1; index < this.totalRecords; index++) {
                $('.record-'+Number(index)).css('display', 'none');
            }
            $('.record-'+Number(page)).css('display', 'table-row');
            this.loader2 = false
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