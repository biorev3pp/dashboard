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
                         <span class="filter-btns row" v-for="seq in sequence" :key="seq">
                            <span  class="text-dark mx-1 pointer-hand wf-180"> 
                                <b v-if="seq == 'agentName'">AGENT: {{ form.agentName }}</b>
                                <b v-else-if="seq == 'disposition'">DISPO: {{ form.disposition }}</b>
                                <b v-else-if="seq == 'talk_time'"> TALK-TIME: 
                                    <em v-if="form.talk_time == 5">0-5 Sec</em>
                                    <em v-if="form.talk_time == 10">6-10 Sec</em>
                                    <em v-if="form.talk_time == 20">11-20 Sec</em>
                                    <em v-if="form.talk_time == '20+'">20+ Sec</em>
                                </b>
                                <b v-else-if="seq == 'acw'">ACW: 
                                    <em v-if="form.acw == 1">0-1 Mins</em>
                                    <em v-if="form.acw == 2">1-2 Mins</em>
                                    <em v-if="form.acw == 3">2-3 Mins</em>
                                    <em v-if="form.acw == '3+'">3+ Mins</em>
                                </b>
                            </span>
                            <i v-if="seq == 'agentName'" class="bi bi-x pr-1  pointer-hand" @click="removeAgentName(1)"></i>
                            <i v-else-if="seq == 'disposition'" class="bi bi-x pr-1  pointer-hand" @click="removeDisposition(1)"></i>
                            <i v-else-if="seq == 'talk_time'" class="bi bi-x pr-1  pointer-hand" @click="removeTalkTime(1)"></i>
                            <i v-else-if="seq == 'acw'" class="bi bi-x pr-1  pointer-hand" @click="removeACW(1)"></i>
                        </span>
                    </div>
                    <div class="col-md-2 col-12 text-right">
                        <button class="btn btn-danger theme-btn icon-left-btn" type="button" @click="getGraphData">
                            <i class="bi bi-bootstrap-reboot"></i> Reset
                        </button>
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
                                        Agent Call Status
                                        <span class="float-right">
                                            <toggle-button v-model="form.only_agent" @change="getGraphData" :margin="3" :width="130" :height="20" :labels="{checked: 'Agents Only', unchecked: 'Agents & None'}" :switch-color="{checked: '#800080', unchecked: '#27408B'}" :color="{checked: '#E599E5', unchecked: '#4E9FFE'}" />
                                            <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/call-details?'+JSON.stringify({sequence:sequence,talk_time_plus:form.talk_time_plus, acw_plus:form.acw_plus,agentName:form.agentName,disposition:form.disposition,talk_time:form.talk_time,acw:form.acw,stime:stime,etime: etime,sdate:sdate,edate:edate}) ">
                                                <i class="bi bi-eye"></i> View
                                            </router-link>
                                        </span>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAgentCallStatus.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAgentCallStatus" 
                                        :series="seriesAgentCallStatus" 
                                        @dataPointSelection="dataPointSelectionHandlerAgentInfo">
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
                                        v-if="labelAgentOccupancy.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAgentOccupancy" 
                                        :series="seriesAgentOccupancy">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Highlights
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="agent-info">
                                        <apexchart 
                                        v-if="labelAgentInfo.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAgentInfo" 
                                        :series="seriesAgentInfo">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Dispositions Status
                                        <span class="float-right">
                                            <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/call-details?'+JSON.stringify({sequence:sequence,talk_time_plus:form.talk_time_plus,acw_plus:form.acw_plus,agentName:form.agentName,disposition:form.disposition,talk_time:form.talk_time,acw:form.acw,stime:stime,etime:etime,sdate:sdate,edate:edate})">
                                                <i class="bi bi-eye"></i> View
                                            </router-link>
                                        </span>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-disposition-status">
                                        <apexchart 
                                        v-if="labelAgentDispositionStatus.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsDispositionStatus" 
                                        :series="seriesDispositionStatus"
                                        @dataPointSelection="dataPointSelectionHandlerAgentBasedDisposition">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Agent Based Talktime
                                        <span class="float-right">
                                            <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/call-details?'+JSON.stringify({sequence:sequence,talk_time_plus:form.talk_time_plus,acw_plus:form.acw_plus,agentName:form.agentName,disposition:form.disposition,talk_time:form.talk_time,acw:form.acw,stime:stime,etime:etime,sdate:sdate,edate:edate})">
                                                <i class="bi bi-eye"></i> View
                                            </router-link>
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
                                        @dataPointSelection="dataPointSelectionHandlerAgentBasedTalkTime"
                                        ></apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Agent Based ACW
                                        <span class="float-right">
                                            <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/call-details?'+JSON.stringify({sequence:sequence,talk_time_plus:form.talk_time_plus,acw_plus:form.acw_plus,agentName:form.agentName,disposition:form.disposition,talk_time:form.talk_time,acw:form.acw,stime:stime,etime:etime,sdate:sdate,edate:edate})">
                                                <i class="bi bi-eye"></i> View
                                            </router-link>
                                        </span>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-based-ACW">
                                        <apexchart 
                                        v-if="labelAgentBasedACW.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAgentBasedACW" 
                                        :series="seriesAgentBasedACW"
                                        @dataPointSelection="dataPointSelectionHandlerAgentBasedACW"
                                        ></apexchart>
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
            sequence : [],
            loader:false,
            records:{},
            form: new Form({
                dateRange:{},
                agentName : null,
                disposition : null,
                talk_time : null,
                talk_time_plus : 0,
                acw : null,
                acw_plus : 0,
                only_agent:false
            }),
            loader_url: '/img/spinner.gif',
            stime : null,
            etime : null,
            sdate : null,
            edate : null,
            talk_time : null,
            acw : null,

            labelAgentInfo : [],
            sAgentInfo : [],

            labelAgentOccupancy : [],
            sAgentOccupancy : [],

            agentName:[],
            dispositins : [],
            talkTimeAgentName: [],
            ACWAgentName: [],

            labelAgentCallStatus : [],
            sAgentCallStatus : [],

            labelAgentDispositionStatus : [],
            sAgentDispositionStatus : [],

            labelAgentBasedTalkTime : [],
            sAgentBasedTalkTime : [],

            labelAgentBasedACW : [],
            sAgentBasedACW : [],

            labelAgentBasedWaitingTime : [],
            sAgentBasedWaitingTime : [],
        }
    },
    computed: {
        seriesAgentInfo(){
            if(this.sAgentInfo.length > 0){
                return this.sAgentInfo
            }else{
                return []
            }
        },
        chartOptionsAgentInfo(){
            if(this.labelAgentInfo.length > 0){
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
                            labels: this.labelAgentInfo,
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
        seriesAgentOccupancy(){
            if(this.sAgentOccupancy.length > 0){
                return this.sAgentOccupancy
            }else{
                return []
            }
        },
        chartOptionsAgentOccupancy(){
            if(this.labelAgentOccupancy.length > 0){
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
                            labels: this.labelAgentOccupancy,
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
        seriesAgentCallStatus(){
            if(this.sAgentCallStatus.length > 0){
                return this.sAgentCallStatus
            }else{
                return []
            }
        },
        chartOptionsAgentCallStatus(){
            if(this.labelAgentCallStatus.length > 0){
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
                            labels: this.labelAgentCallStatus,
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
        seriesDispositionStatus(){
            if(this.sAgentDispositionStatus.length > 0){
                return this.sAgentDispositionStatus
            }else{
                return []
            }
        },
        chartOptionsDispositionStatus(){
            if(this.labelAgentDispositionStatus.length > 0){
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
                            labels: this.labelAgentDispositionStatus,
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
        seriesAgentBasedACW(){
            if(this.sAgentBasedACW.length > 0){
                return this.sAgentBasedACW
            }else{
                return []
            }
        },
        chartOptionsAgentBasedACW(){
            if(this.labelAgentBasedACW.length > 0){
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
                            labels: this.labelAgentBasedACW,
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
        removeAgentName(typ){
            if(this.sequence.indexOf("agentName") != -1){
                this.sequence.splice(this.sequence.indexOf("agentName"), 1)
            }
            this.form.agentName = null
            if(typ == 1) {
                this.runAll()
            }
        },
        removeDisposition(typ){
            if(this.sequence.indexOf("disposition") != -1){
                this.sequence.splice(this.sequence.indexOf("disposition"), 1)
            }
            this.form.disposition = null
            if(typ == 1) {
                this.runAll()
            }
        },
        removeTalkTime(typ){
            this.form.talk_time = null
            this.form.talk_time_plus = 0
            if(this.sequence.indexOf("talk_time") != -1){
                this.sequence.splice(this.sequence.indexOf("talk_time"), 1)
            }
            if(typ == 1) {
                this.runAll()
            }
        },
        removeACW(typ){
            this.form.acw = null
            this.form.acw_plus = 0
            if(this.sequence.indexOf("acw") != -1){
                this.sequence.splice(this.sequence.indexOf("acw"), 1)
            }

            if(typ == 1) {
                this.runAll()
            }
        },
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        getGraphData(){
            this.removeAgentName()
            this.removeDisposition()
            this.removeTalkTime()
            this.removeACW()
            this.labelAgentInfo = []
            this.sAgentInfo = []
            this.labelAgentOccpancy = []
            this.sAgentOccpancy = []
            this.agentOccupancy()
            this.agentCallStatus()
            this.agentDispositionStatus()
            this.agentBasedTalkTime()
            this.agentBasedACW()
            this.agentBasedWaitingTime()
        },
        dataPointSelectionHandlerAgentInfo(e, chartContext, config) {
            if(this.sequence.indexOf("agentName") != -1){
                this.sequence.splice(this.sequence.indexOf("agentName"), 1)
            }
            if(this.sequence.indexOf("agentName") == -1){
                this.sequence.push("agentName")
            }
            
            this.removeDisposition()
            this.removeTalkTime()
            this.removeACW()
            this.labelAgentInfo = []
            this.sAgentInfo = []
            this.form.agentName = this.agentName[config.dataPointIndex]
            this.form.post("/api/get-agent-info").then( (response) => {
                this.labelAgentInfo = response.data.label
                this.sAgentInfo = response.data.series
                this.stime = response.data.stime
                this.etime = response.data.etime
                this.sdate = response.data.sdate
                this.edate = response.data.edate
                this.loader = false
            })
            this.agentOccupancy()
            this.runAll()
            this.loader = false
        },        
        agentOccupancy(){
            this.loader = false
            this.labelAgentOccupancy = []
            this.sAgentOccupancy = []
            this.form.post("/api/get-agent-occupancy-data").then( (response) => {
                this.labelAgentOccupancy = response.data.label
                this.sAgentOccupancy = response.data.series
                this.loader = false
            })
        },
        agentCallStatus(){
            this.loader = false
            this.labelAgentCallStatus = []
            this.sAgentCallStatus = []
            this.agentName = []
            this.form.post("/api/get-agent-call-status").then( (response) => {
                this.labelAgentCallStatus = response.data.label
                this.sAgentCallStatus = response.data.series
                this.agentName = response.data.agentName
                this.stime = response.data.stime
                this.etime = response.data.etime
                this.sdate = response.data.sdate
                this.edate = response.data.edate
                this.loader = false
            })
        },
        dataPointSelectionHandlerAgentBasedDisposition(e, chartContext, config){
            if(this.sequence.indexOf("disposition") != -1){
                this.sequence.splice(this.sequence.indexOf("disposition"), 1)
            }
            if(this.sequence.indexOf("disposition") == -1){
                this.sequence.push("disposition")
            }
            this.form.disposition = this.dispositins[config.dataPointIndex]
            this.runAll()
        },
        agentDispositionStatus(){
            this.loader = false
            this.labelAgentDispositionStatus = []
            this.sAgentDispositionStatus = []
            this.form.post("/api/get-agent-disposition-status").then( (response) => {
                this.labelAgentDispositionStatus = response.data.label
                this.sAgentDispositionStatus = response.data.series
                this.dispositins = response.data.dispositins
                this.stime = response.data.stime
                this.etime = response.data.etime
                this.sdate = response.data.sdate
                this.edate = response.data.edate
                this.loader = false
            })
        },
        dataPointSelectionHandlerAgentBasedTalkTime(e, chartContext, config){
            if(this.sequence.indexOf("talk_time") != -1){
                this.sequence.splice(this.sequence.indexOf("talk_time"), 1)
            }
            if(this.sequence.indexOf("talk_time") == -1){
                this.sequence.push("talk_time")
            }
            if(this.talk_time){
                if(this.talk_time[config.dataPointIndex] == "20+"){
                    this.form.talk_time_plus = 1    
                }else{
                    this.form.talk_time_plus = 0
                }
                this.form.talk_time = this.talk_time[config.dataPointIndex]

            }else{
                this.form.agentName =  this.talkTimeAgentName[config.dataPointIndex]
            }
            this.runAll()
        },
        agentBasedTalkTime(){
            this.loader = false
            this.labelAgentBasedTalkTime = []
            this.sAgentBasedTalkTime = []
            this.form.post("/api/get-agent-based-talk-time").then( (response) => {
                this.labelAgentBasedTalkTime = response.data.label
                this.sAgentBasedTalkTime = response.data.series
                this.talk_time = response.data.talk_time
                this.talkTimeAgentName = response.data.agentsName
                this.stime = response.data.stime
                this.etime = response.data.etime
                this.sdate = response.data.sdate
                this.edate = response.data.edate
                this.loader = false
            })
        },
        dataPointSelectionHandlerAgentBasedACW(e, chartContext, config){
            if(this.sequence.indexOf("acw") != -1){
                this.sequence.splice(this.sequence.indexOf("acw"), 1)
            }
            if(this.sequence.indexOf("acw") == -1){
                this.sequence.push("acw")
            }
            if(this.acw){
                if(this.acw[config.dataPointIndex] == "3+"){
                    this.form.acw_plus = 1
                }else{
                    this.form.acw_plus = 0
                }
                this.form.acw = this.acw[config.dataPointIndex]
            }else{
                this.form.agentName =  this.ACWAgentName[config.dataPointIndex]
            }
            this.runAll()
        },
        agentBasedACW(){
            this.loader = false
            this.labelAgentBasedACW = []
            this.sAgentBasedACW = []
            this.form.post("/api/get-agent-based-ACW").then( (response) => {
                this.labelAgentBasedACW = response.data.label
                this.sAgentBasedACW = response.data.series
                this.ACWAgentName = response.data.agentsName
                this.acw = response.data.acw
                this.stime = response.data.stime
                this.etime = response.data.etime
                this.sdate = response.data.sdate
                this.edate = response.data.edate
                this.loader = false
            })
        },
        agentBasedWaitingTime(){
            this.loader = false
            this.labelAgentBasedWaitingTime = []
            this.sAgentBasedWaitingTime = []
            this.form.post("/api/get-agent-based-waiting-time").then( (response) => {
                this.labelAgentBasedWaitingTime = response.data.label
                this.sAgentBasedWaitingTime = response.data.series
                this.stime = response.data.stime
                this.etime = response.data.etime
                this.sdate = response.data.sdate
                this.edate = response.data.edate
                this.loader = false
            })
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