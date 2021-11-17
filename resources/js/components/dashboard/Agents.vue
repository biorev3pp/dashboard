<template>
    <div>
        <div>
            <div class="filterbox p-2 border-top" style="background:transparent">
                <div class="row m-0">
                    <div class="col-md-3 col-6">
                        <date-range-picker ref="picker" :locale-data="{ firstDay: 1, format: 'dd-mm-yyyy HH:mm:ss' }" :timePicker24Hour="false" :showWeekNumbers="true" :showDropdowns="true"  :dateFormat="dateFormat" :autoApply="false" v-model="form.dateRange" @update="getGraphData">
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
                    <div class="col-md-7 col-12">
                         <span class="filter-btns row mb-0" v-if="form.agentName">
                            <span  class="text-dark mx-1 pointer-hand wf-180"> 
                                <b>AGENT: {{ form.agentName | uppercase }}</b>
                            </span>
                        </span>
                    </div>
                    <div class="col-md-2 col-12 text-right">
                        
                    </div>
                </div>
            </div>
            <div class="calls-chart-div border-top p-1">
                <!-- <div class="chart-box"> -->
                    <div class="row m-0">
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Agent Login Status
                                        <button type="button" class="float-right btn btn-sm btn-primary px-1" v-show="form.agentName" @click="AllAgents()">
                                            All Agents
                                        </button>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAgentLognTime.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAgentLoginTime" 
                                        :series="seriesAgentLoginTime" 
                                        @dataPointSelection="dataPointSelectionHandlerAgentLoginTime">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Agent Occupancy
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div>
                                        <apexchart 
                                        v-if="labelAgentAvailableTime.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAgentAvailableTime" 
                                        :series="seriesAgentAvailableTime">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Agent Proficiency
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="agent-info">
                                        <apexchart 
                                        v-if="labelAgentCallTime.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAgentCallTime" 
                                        :series="seriesAgentCallTimeStatus">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Talktime
                                        <span class="float-right">
                                            
                                        </span>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-based-talk-time">
                                        <apexchart 
                                        v-if="labelAgentBasedTalkTime.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAgentBasedTalkTime" 
                                        :series="seriesAgentBasedTalkTime"
                                        ></apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Total Calls
                                        <span class="float-right">
                                            {{ totalCalls }}
                                        </span>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <table class="table table-striped">
                                        <tr v-for="(agent,index) in agentCallCount" :key="index">
                                            <th>{{ agent.name | uppercase}}</th><td>{{ agent.total }}</td>
                                        </tr>
                                    </table>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Productivity
                                        <span class="float-right">
                                            
                                        </span>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="">
                                        <apexchart 
                                        v-if="labelAgentProductivityTime.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsProductivityTime" 
                                        :series="seriesAgentProductivityTime">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- </div> -->
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
import { ToggleButton } from 'vue-js-toggle-button'
export default {
    components:{DateRangePicker, ToggleButton},
    data() {
        return {
            // startDate : '',
            // endDate : '',
            sequence : [],
            loader:false,
            records:{},
            totalCalls : 0,
            agentCallCount : [],
            dispositionCallCount : [],
            form: new Form({
                dateRange:{},
                agentName : null,
                time_limit : false
            }),
            loader_url: '/img/spinner.gif',
            stime : null,
            etime : null,
            sdate : null,
            edate : null,
            talk_time : null,
            acw : null,

            sAgentInfo : [],

            labelAgentCallTime : [],
            sAgentCallTime : [],

            labelAgentAvailableTime : [],
            sAgentOccupancy : [],

            agentName:[],
            dispositins : [],
            talkTimeAgentName: [],
            ACWAgentName: [],
            

            labelAgentLognTime : [],
            sAgentCallStatus : [],

            labelAgentProductivityTime : [],
            sAgentDispositionStatus : [],

            labelAgentBasedTalkTime : [],
            sAgentBasedTalkTime : [],

            labelAgentAfterCallWork : [],
            sAgentBasedACW : [],

            labelAgentBasedWaitingTime : [],
            sAgentBasedWaitingTime : [],
        }
    },
    computed: {

        seriesAgentLoginTime(){
            if(this.sAgentCallStatus.length > 0){
                return this.sAgentCallStatus
            }else{
                return []
            }
        },
        chartOptionsAgentLoginTime(){
            if(this.labelAgentLognTime.length > 0){
                return {
                            tooltip: {
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    return (
                                        w.globals.labels[seriesIndex]
                                    );
                                }
                            },
                            stroke: {
                                show: false,
                                curve: 'stepline',
                                lineCap: 'butt',
                                width: 2,
                                dashArray: 0,      
                            },
                            chart: {
                                width: '98%',
                                type: 'pie',
                            },
                            labels: this.labelAgentLognTime,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                    width: 200
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
        seriesAgentAvailableTime(){
            if(this.sAgentOccupancy.length > 0){
                return this.sAgentOccupancy
            }else{
                return []
            }
        },
        chartOptionsAgentAvailableTime(){
            if(this.labelAgentAvailableTime.length > 0){
                return {
                            tooltip: {
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    return (
                                        w.globals.labels[seriesIndex]
                                    );
                                }
                            },
                            stroke: {
                                show: false,
                                curve: 'stepline',
                                lineCap: 'butt',
                                width: 2,
                                dashArray: 0,
                            },
                            chart: {
                                width: '98%',
                                type: 'pie',
                            },
                            labels: this.labelAgentAvailableTime,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: 200
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
        seriesAgentCallTimeStatus(){
            if(this.sAgentCallTime.length > 0){
                return this.sAgentCallTime
            }else{
                return []
            }
        },
        chartOptionsAgentCallTime(){
            if(this.labelAgentCallTime.length > 0){
                return {
                            tooltip: {
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    return (
                                        w.globals.labels[seriesIndex]
                                    );
                                }
                            },
                            stroke: {
                                show: false,
                                curve: 'stepline',
                                lineCap: 'butt',
                                width: 2,
                                dashArray: 0,
                            },
                            chart: {
                                width: '98%',
                                type: 'pie',
                            },
                            labels: this.labelAgentCallTime,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: 200
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
        seriesAgentProductivityTime(){
            if(this.sAgentDispositionStatus.length > 0){
                return this.sAgentDispositionStatus
            }else{
                return []
            }
        },
        chartOptionsProductivityTime(){
            if(this.labelAgentProductivityTime.length > 0){
                return {
                            tooltip: {
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    return (
                                        w.globals.labels[seriesIndex]
                                    );
                                }
                            },
                            stroke: {
                                show: false,
                                curve: 'stepline',
                                lineCap: 'butt',
                                width: 2,
                                dashArray: 0,      
                            },
                            chart: {
                            width: '98%',
                            type: 'pie',
                            },
                            labels: this.labelAgentProductivityTime,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                    width: 200
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
        seriesAgentBasedTalkTime(){
            if(this.sAgentBasedTalkTime.length > 0){
                return this.sAgentBasedTalkTime
            }else{
                return []
            }
        },
        chartOptionsAgentBasedTalkTime(){
            if(this.labelAgentBasedTalkTime.length > 0){
                return {    
                    tooltip: {
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    return (
                                        w.globals.labels[seriesIndex]
                                    );
                                }
                            },
                            stroke: {
                                show: false,
                                curve: 'stepline',
                                lineCap: 'butt',
                                width: 2,
                                dashArray: 0,      
                            },
                            chart: {
                            type: 'pie',
                            },
                            labels: this.labelAgentBasedTalkTime,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                    width: 200
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
        seriesAgentAfterCallWork(){
            if(this.sAgentBasedACW.length > 0){
                return this.sAgentBasedACW
            }else{
                return []
            }
        },
        chartOptionsAgentAfterCallWork(){
            if(this.labelAgentAfterCallWork.length > 0){
                return {
                            tooltip: {
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    return (
                                        w.globals.labels[seriesIndex]
                                    );
                                }
                            },
                            stroke: {
                                show: false,
                                curve: 'stepline',
                                lineCap: 'butt',
                                width: 2,
                                dashArray: 0,      
                            },
                            chart: {
                                width: '98%',
                                type: 'pie',
                            },
                            labels: this.labelAgentAfterCallWork,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                    width: 200
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
        seriesAgentBasedWaitingTime(){
            if(this.sAgentBasedWaitingTime.length > 0){
                return this.sAgentBasedWaitingTime
            }else{
                return []
            }
        },
        chartOptionsAgentBasedWaitingTime(){
            if(this.labelAgentBasedWaitingTime.length > 0){
                return {
                            tooltip: {
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    return (
                                        w.globals.labels[seriesIndex]
                                    );
                                }
                            },
                            stroke: {
                                show: false,
                                curve: 'stepline',
                                lineCap: 'butt',
                                width: 2,
                                dashArray: 0,      
                            },
                            chart: {
                            width: '98%',
                            type: 'pie',
                            },
                            labels: this.labelAgentBasedWaitingTime,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                    width: 200
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
        AllAgents() {
            this.form.agentName = null;
            this.getGraphData()
        },
        agentLoginStatus(){
            this.loader = false
            this.labelAgentLognTime = []
            this.sAgentCallStatus = []
            this.agentName = []
            this.form.post("/api/get-agent-occupancy-login_time").then( (response) => {
                this.labelAgentLognTime = response.data.label
                this.sAgentCallStatus = response.data.series
                this.agentName = response.data.agentName
                this.loader = false
            })
        }, 
        agentOccupancy(){
            this.loader = false
            this.labelAgentAvailableTime = []
            this.sAgentOccupancy = []
            this.form.post("/api/get-agent-occupancy-available-time").then( (response) => {
                this.labelAgentAvailableTime = response.data.label
                this.sAgentOccupancy = response.data.series
                // this.$refs.picker.startDate = new Date(response.data.startDate)
                // this.$refs.picker.endDate = new Date(response.data.endDate)
                this.form.dateRange = {startDate : new Date(response.data.startDate), endDate:new Date(response.data.endDate)}
                this.loader = false
                // console.log(this.$refs.picker.startDate)
                // console.log(this.$refs.picker.endDate)
                // this.$refs.picker.togglePicker(false)
            })
        },
        agentProficiency(){
            this.loader = false
            this.labelAgentCallTime = []
            this.sAgentCallTime = []
            this.form.post("/api/get-agent-proficiency").then( (response) => {
                this.labelAgentCallTime = response.data.label
                this.sAgentCallTime = response.data.series
                this.loader = false
            })
        },
        agentBasedTalkTime(){
            this.loader = false
            this.labelAgentBasedTalkTime = []
            this.sAgentBasedTalkTime = []
            this.form.post("/api/get-agent-based-talk-time-status").then( (response) => {
                this.labelAgentBasedTalkTime = response.data.label
                this.sAgentBasedTalkTime = response.data.series
                this.totalCalls = response.data.agentCallCount.totalCall
                this.agentCallCount = response.data.agentCallCount.records
            })
        },
        agentBasedACW(){
            this.loader = false
            this.labelAgentAfterCallWork = []
            this.sAgentBasedACW = []
            this.form.post("/api/get-total-call-and-disposition-total-call").then( (response) => {
                this.labelAgentAfterCallWork = response.data.label
                this.sAgentBasedACW = response.data.series
                
                this.loader = false
            })
        },
         agentWaitingTime(){
            this.loader = false
            this.labelAgentProductivityTime = []
            this.sAgentDispositionStatus = []
            this.form.post("/api/get-agent-productivity-time").then( (response) => {
                this.labelAgentProductivityTime = response.data.label
                this.sAgentDispositionStatus = response.data.series
                this.loader = false
            })
        },
        dateFormat(classes, date) {
            // console.log(classes)
            // console.log(date)
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        getGraphData(){
            this.labelAgentCallTime = []
            this.sAgentInfo = []
            this.labelAgentOccpancy = []
            this.sAgentOccpancy = []
            this.agentLoginStatus()
            this.agentOccupancy()
            this.agentProficiency()
            this.agentBasedTalkTime()
            // this.agentBasedACW()
            this.agentWaitingTime()
        },
        dataPointSelectionHandlerAgentLoginTime(e, chartContext, config) {
           
            this.form.agentName = this.agentName[config.dataPointIndex]
            if(this.form.agentName){
                this.agentOccupancy()
                this.agentProficiency()
                this.agentBasedTalkTime()
                // this.agentBasedACW()
                this.agentWaitingTime()
                this.loader = false
            }
        },
        runAll(){
            if(this.sequence.indexOf("agentName") != -1){

            }
            if(this.sequence.indexOf("disposition") == -1){
                this.agentDispositionStatus()
            }
            if(this.sequence.indexOf("talk_time") == -1){
                this.agentBasedTalkTime()
            }
            if(this.sequence.indexOf("acw") == -1){
                this.agentBasedACW()
            }
        },
    },
    created() {
        
    },
    mounted() {
        
        // this.$refs.picker.togglePicker(true)
        this.getGraphData()
    }
}
</script>
<style>
.apexcharts-tooltip.apexcharts-theme-dark {
    color: #000 !important;
    background: transparent !important
}
.apexcharts-tooltip {
    border-radius: 5px;
    box-shadow: 2px 2px 2px -4px #999 !important;
    cursor: default;
    font-size: 10px;
    left: 62px;
    opacity: 0;
    pointer-events: none;
    position: absolute;
    top: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    white-space: nowrap;
    z-index: 12;
    transition: 0.15s ease all;
    font-weight: 500;
}
.apexcharts-legend-text{
    color: #000 !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    font-family: inherit !important;
}
</style>