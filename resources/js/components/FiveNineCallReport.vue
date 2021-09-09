<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-4 col-12 pl-0">
                    <div>
                        <!-- <pre class="language-json">
                            <span v-for="v in value" :key="v.agent_id">{{ v.first_name }}</span>
                        </pre> -->
                        <multiselect v-model="value" :options="agentsList" :multiple="true" :close-on-select="false" :clear-on-select="false" :preserve-search="false" placeholder="Select Agent" label="name" track-by="name" :preselect-first="true">
                            <!-- <template slot="selection" slot-scope="{ values, search, isOpen }">
                                <span class="multiselect__single" v-if="values.length &amp;&amp; !isOpen">
                                    <span class="agent-item" v-for="v in value" :key="v.agent_id">{{ v.first_name }} {{ v.last_name }}</span></span>
                            </template> -->
                        </multiselect>
                    </div>
                </div>
                <div class="col-md-4 col-12 pl-0">
                    <div class="form-group">
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
                <div class="col-md-4 col-12 p-0">
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="getCallReport">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                        <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z"/>
                        </svg>
                        Filter 
                    </button>
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="reset">
                         Reset
                    </button> 
                    <download-excel
                        class="btn btn-primary icon-btn-right theme-btn mr-3"
                        :data="json_data"
                        :fields="json_fields"
                        :before-generate = "startDownload"
                        :before-finish   = "finishdownload"
                        worksheet="prospect-export"
                        :name="filename"
                        type="csv"
                    >
                    <i class="bi bi-file-arrow-down"></i> Export
                    </download-excel>
                </div>
            </div>
            <p v-if="recordsCount == 0" class="text-danger">Please select agent and date</p>
        </div>
        <div class="mapping-div">
            <table v-if="recordsCount > 0" class="table table-bordered table-striped">
                <thead>
                    <tr style="height: 18pt">
                        <td>AGENT NAME</td>
                        <td>LOGIN TIME</td>
                        <td>NOT READY TIME</td>
                        <td>Average NOT READY TIME</td>
                        <td>NOT READY TIME count</td>
                        <td>AVAILABLE TIME (LOGIN LESS NOT READY)</td>
                        <td>CALLS count</td>
                        <td>ON CALL TIME</td>
                        <td>Average ON CALL TIME</td>
                        <td>TALK TIME</td>
                        <td>Average TALK TIME</td>
                        <td>AFTER CALL WORK TIME</td>
                        <td>Average AFTER CALL WORK TIME</td>
                    </tr>
                </thead>
                <tbody v-for="(call,index) in calls" :key="'row-' + index">
                        <tr>
                            <td colspan="13"><b> Date : {{ index }} </b></td>
                        </tr>
                        <tr v-for="(c, cindex) in call" :key="'row-' + index + cindex">
                            <td> 
                                <span v-if="c.agent_first_name">
                                    <span v-if="c.agent == '[Deleted]'">[Deleted]</span>
                                    <span v-else>{{ c.agent_first_name }} {{ c.agent_last_name }}</span>
                                    
                                </span>
                                <span v-else>
                                    <span v-if="c.total == 'total'"><b>Total</b></span>
                                    <span v-else>[Deleted]</span>
                                </span>
                            </td>
                            <td>
                                <b v-if="c.total == 'total'"> {{ c.login_time }} </b>
                                <span v-else> {{ c.login_time }} </span>
                            </td>
                            <td> 
                                <b v-if="c.total == 'total'"> {{ c.not_ready_time }} </b>
                                <span v-else> {{ c.not_ready_time }} </span>
                            </td>
                            <td> <b v-if="c.total == 'total'">Avg : {{ c.average_not_ready_time }} </b>
                                <span v-else> {{ c.average_not_ready_time }} </span>
                            </td>
                            <td> 
                                <b v-if="c.total == 'total'"> {{ c.not_ready_time_count }} </b>
                                <span v-else> {{ c.not_ready_time_count }} </span>
                            </td>
                            <td> 
                                <b v-if="c.total == 'total'"> {{ c.login_less_not_ready }} </b>
                                <span v-else> {{ c.login_less_not_ready }} </span>
                            </td>
                            <td> 
                                <b v-if="c.total == 'total'"> {{ c.calls_count }} </b>
                                <span v-else> {{ c.calls_count }} </span>
                            </td>
                            <td> 
                                <b v-if="c.total == 'total'"> {{ c.on_call_time }} </b>
                                <span v-else> {{ c.on_call_time }} </span>
                            </td>
                            <td> 
                                <b v-if="c.total == 'total'">Avg : {{ c.average_on_call_time }} </b>
                                <span v-else> {{ c.average_on_call_time }} </span>
                            </td>
                            <td> <b v-if="c.total == 'total'"> {{ c.talk_time }} </b>
                                <span v-else> {{ c.talk_time }} </span>
                            </td>
                            <td> <b v-if="c.total == 'total'">Avg : {{ c.average_talk_time }} </b>
                                <span v-else> {{ c.average_talk_time }} </span>
                            </td>
                            <td> <b v-if="c.total == 'total'"> {{ c.after_call_work_time }} </b>
                                <span v-else> {{ c.after_call_work_time }} </span>
                            </td>
                            <td> <b v-if="c.total == 'total'">Avg : {{ c.average_after_call_work_time }} </b>
                                <span v-else> {{ c.average_after_call_work_time }} </span>
                            </td>
                        </tr>
                </tbody>
            </table>
            <table v-else class="table table-bordered table-striped">
                <tr><td colspan="13">No data available</td></tr>
            </table>
        </div>
    </div>
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import downloadExcel from "vue-json-excel";
import Multiselect from 'vue-multiselect';

export default {
    components: { DateRangePicker, downloadExcel, Multiselect },
    data() {
        return {
            value: [],
            loader:false,
            swap : false,
            loader_url: '/img/spinner.gif',
            agentsList : [],
            agentArray : [],
            calls : [],
            header : [],
            paginationArray : [],
            per_page : 10,
            total_page : 0,
            pno : 1,
            filename: '',
            form : new Form({
                agent : 0,
                dateRange : {},
                email : '',
                id : '',
                agent_id : [],
            }),
            json_fields: {
                "Date" : "date",
                "Agent Name": "agent_first_name",
                "LOGIN TIME" : "login_time",
                "NOT READY TIME": "not_ready_time",
                "Average NOT READY TIME" : "average_not_ready_time", 
                "NOT READY TIME count" : "not_ready_time_count",
                "AVAILABLE TIME (LOGIN LESS NOT READY)" : "login_less_not_ready",
                "CALLS count" : "calls_count",
                "ON CALL TIME" : "on_call_time",
                "Average ON CALL TIME" : "average_on_call_time",
                "TALK TIME'" : "talk_time",
                "Average TALK TIME" : "average_talk_time",
                "AFTER CALL WORK TIME" : "after_call_work_time",
                "Average AFTER CALL WORK TIME" : "average_after_call_work_time"
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
            recordsCount : 0,
        }
    },
    computed: {
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
            this.value = []
            this.json_data = []
            this.calls = []
            this.form.agent = 0
            this.form.dateRange = {}
            this.form.email = ''
            this.form.id = ''
            this.form.agent_id = []
            this.recordsCount = 0
        },
        startDownload(){
            let date = new Date();
            this.filename = 'five9-call-report-'+date.getMonth()+'-'+date.getDate()+'-'+date.getFullYear()+'-'+date.getHours()+'-'+date.getMinutes()+'-'+date.getSeconds()+'.csv';
            var calls = this.calls
            var counter = 0
            for(const key in calls){
                for(const kkey in calls[key]){
                    if(calls[key][kkey]["agent_first_name"]){
                        var name = calls[key][kkey]["agent_first_name"]+' '+calls[key][kkey]["agent_last_name"]
                    }
                    if(calls[key][kkey]["total"]){
                        var name = 'Total'
                    }
                    if(calls[key][kkey]["agent"] == '[Deleted]'){
                        var name = '[Deleted]';
                    }
                    this.json_data[counter++] = {
                        "date" : key,
                        "agent_first_name" : name,
                        "login_time" : calls[key][kkey]["login_time"],
                        "not_ready_time" : calls[key][kkey]["not_ready_time"],
                        "average_not_ready_time" : calls[key][kkey]["average_not_ready_time"],
                        "not_ready_time_count" : calls[key][kkey]["not_ready_time_count"],
                        "login_less_not_ready" : calls[key][kkey]["login_less_not_ready"],
                        "calls_count" : calls[key][kkey]["calls_count"],
                        "on_call_time" : calls[key][kkey]["on_call_time"],
                        "average_on_call_time" : calls[key][kkey]["average_on_call_time"],
                        "talk_time" : calls[key][kkey]["talk_time"],
                        "average_talk_time" : calls[key][kkey]["average_talk_time"],
                        "after_call_work_time" : calls[key][kkey]["after_call_work_time"],
                        "average_after_call_work_time" : calls[key][kkey]["average_after_call_work_time"],
                    }
                }
            }
        },
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        getCallReport(){
            this.calls = []
            this.form.agent_id = []
            if(this.value.length > 0){
                for (const key in this.value) {
                    this.form.agent_id[key] = this.value[key]['agent_id'];
                }
            }

            this.$Progress.start()

            this.form.post('/api/get-call-reports').then((response) => {                
                if(response.data.hasOwnProperty("id")){
                    var id = response.data.id;
                    this.form.id = id
                    axios.get('/api/get-five-nine-report-response/' + id).then((responseR) => {
                        if(responseR.data.result == true){
                            
                            this.form.post('/api/get-five-nine-call-log-report-result').then((responseReport) => {
                                this.loader = false
                                if(responseReport.data.results == false){

                                } else {
                                    this.header = responseReport.data.header
                                    this.calls = responseReport.data.allRecords                                
                                    this.recordsCount = responseReport.data.recordsCount
                                    var message = this.recordsCount + ' Records found !! !!'
                                    this.$Progress.finish()
                                    Vue.$toast.info(this.recordsCount + ' Records found !! !!');
                                }
                            });
                        }  
                    })
                } else {
                    this.loader = false
                    this.$swal({
                        icon: 'error',
                        title: 'Please try again',
                    })
                }
            })
        },
        getAgentsInformation(){
            axios.get('/api/get-agents-information').then((response) => {
                this.agentsList = response.data.results
            })
        },
        pagination(total){
            this.total_page = Math.ceil(total/this.per_page)
            for(var i = 0; i < this.total_page; i++){
                this.paginationArray[i] = i
            }
        },
        showPage(page_no){            
            this.pno = page_no
            $(".record").css("display", "none")
            $(".record-"+page_no).css("display", "table-row")
            var start = 0
            var end = 0
            if(page_no <= 5){
                start = 1
                end = 11
                if(this.total_page <= 11){
                    end = this.total_page
                }
            } else {
                if(this.total_page - page_no > 5){
                    start = page_no-5
                    end = page_no + 5
                } else{
                    start = this.total_page - 11
                    end = this.total_page
                }
            }
            
            $(".page-item").css("display", "none")
            for(var i = start; i <= end; i++){
                $(".page-item-" + i).css("display", "inline-block")
            }
        },
        showMessage(icon, text, message){
            this.$swal({
                icon: icon,
                title: text,
                text: message,
            })
        },
        
        finishdownload(){

        },
    },
    mounted() {
        this.getAgentsInformation()
        //this.getCallReport()

    }
}
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
.multiselect{
        min-height: 18px;
        top : -10px;
}
.multiselect__tags{
    padding-top: 0px;
    min-height: 18px;
    border: 1px solid #ccc;
    margin-top: 10px;
    margin-left: 10px;
}
.multiselect__input{
    min-height: 18px;
}
.multiselect__placeholder{
    padding: 8px 0px 0px 8px;
    font-size: 0.75rem;
    font-weight: 400;
    line-height: 14px;
    color: #495057;
}
.multiselect__option {
    
}
.multiselect__tags-wrap{

}
.multiselect__tag{
    padding : 5px 10px 5px 10px ;
    margin: 5px;
}
.multiselect__select{
    display: block;
}
.agent-item{
    padding: 0 10px 0 10px;
}
.multiselect__tag-icon{
    padding-left: 13px;
}
</style>