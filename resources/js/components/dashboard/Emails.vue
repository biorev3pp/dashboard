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
                        <button class="btn btn-danger theme-btn icon-left-btn" type="button" @click="getGraphData">
                            <i class="bi bi-bootstrap-reboot"></i> Reset
                        </button>
                    </div>
                </div>
                <div class="calls-chart-div border-top p-1">
                <!-- <div class="chart-box"> -->
                    <div class="row m-0">
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        All Emails
                                        <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/email-details?'+JSON.stringify({agentName:form.agentName,dateRange:form.dateRange,type:'All'}) ">
                                            <i class="bi bi-eye"></i> View
                                        </router-link>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAllEmails.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAllEmails" 
                                        :series="seriesAllEmails"
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
                                        Delivered Emails
                                        <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/email-details?'+JSON.stringify({agentName:form.agentName,dateRange:form.dateRange,type:'Delivered'}) ">
                                            <i class="bi bi-eye"></i> View
                                        </router-link>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAllEmailsDelivered.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAllEmailsDelivered" 
                                        :series="seriesAllEmailsDelivered">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Bounced Emails
                                        <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/email-details?'+JSON.stringify({agentName:form.agentName,dateRange:form.dateRange,type:'Bounced'}) ">
                                            <i class="bi bi-eye"></i> View
                                        </router-link>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAllEmailsBounced.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAllEmailsBounced" 
                                        :series="seriesAllEmailsBounced">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Clicked Emails
                                        <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/email-details?'+JSON.stringify({agentName:form.agentName,dateRange:form.dateRange,type:'Clicked'}) ">
                                            <i class="bi bi-eye"></i> View
                                        </router-link>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAllEmailsClicked.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAllEmailsClicked" 
                                        :series="seriesAllEmailsClicked">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Opened Emails
                                        <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/email-details?'+JSON.stringify({agentName:form.agentName,dateRange:form.dateRange,type:'Opened'}) ">
                                            <i class="bi bi-eye"></i> View
                                        </router-link>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAllEmailsOpened.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAllEmailsOpened" 
                                        :series="seriesAllEmailsOpened">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Replied Emails
                                        <router-link target="_blank" class="btn btn-secondary btn-sm theme-btn" :to="'/dashboard/email-details?'+JSON.stringify({agentName:form.agentName,dateRange:form.dateRange,type:'Replied'}) ">
                                            <i class="bi bi-eye"></i> View
                                        </router-link>
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAllEmailsReplied.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAllEmailsReplied" 
                                        :series="seriesAllEmailsReplied">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-lg-4 col-md-6  col-sm-12 col-12 p-a-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="text-capitalize m-0">
                                        Retry Emails
                                    </h5>
                                </div> 
                                <div class="card-body">
                                    <div id="chart-agent-call-status">
                                        <apexchart 
                                        v-if="labelAllEmailsRetry.length > 0" 
                                        type="pie" 
                                        width="98%" 
                                        :options="chartOptionsAllEmailsRetry" 
                                        :series="seriesAllEmailsRetry">
                                        </apexchart>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>
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
import { ToggleButton } from 'vue-js-toggle-button'

export default {
    components:{DateRangePicker, ToggleButton},
    data() {
        return {
            loader:false,
            form: new Form({
                dateRange:{},
                agentName : null,
                time_limit : false
            }),
            loader_url: '/img/spinner.gif',
            labelAllEmails : [],
            sAllEmails : [],
            labelAllEmailsDelivered : [],
            sAllEmailsDelivered : [],
            labelAllEmailsBounced : [],
            sAllEmailsBounced : [],
            labelAllEmailsClicked : [],
            sAllEmailsClicked : [],
            labelAllEmailsOpened : [],
            sAllEmailsOpened : [],
            labelAllEmailsReplied : [],
            sAllEmailsReplied : [],
            labelAllEmailsRetry : [],
            sAllEmailsRetry : [],
            
            agentName : [],
        }
    },
    computed: {
        seriesAllEmails(){
            if(this.sAllEmails.length > 0){
                return this.sAllEmails
            }else{
                return []
            }
        },
        chartOptionsAllEmails(){
            if(this.labelAllEmails.length > 0){
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
                            labels: this.labelAllEmails,
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
        seriesAllEmailsDelivered(){
            if(this.sAllEmailsDelivered.length > 0){
                return this.sAllEmailsDelivered
            }else{
                return []
            }
        },
        chartOptionsAllEmailsDelivered(){
            if(this.labelAllEmailsDelivered.length > 0){
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
                            labels: this.labelAllEmailsDelivered,
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
        seriesAllEmailsBounced(){
            if(this.sAllEmailsBounced.length > 0){
                return this.sAllEmailsBounced
            }else{
                return []
            }
        },
        chartOptionsAllEmailsBounced(){
            if(this.labelAllEmailsBounced.length > 0){
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
                            labels: this.labelAllEmailsBounced,
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
        seriesAllEmailsClicked(){
            if(this.sAllEmailsClicked.length > 0){
                return this.sAllEmailsClicked
            }else{
                return []
            }
        },
        chartOptionsAllEmailsClicked(){
            if(this.labelAllEmailsClicked.length > 0){
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
                            labels: this.labelAllEmailsClicked,
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
        seriesAllEmailsOpened(){
            if(this.sAllEmailsOpened.length > 0){
                return this.sAllEmailsOpened
            }else{
                return []
            }
        },
        chartOptionsAllEmailsOpened(){
            if(this.labelAllEmailsOpened.length > 0){
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
                            labels: this.labelAllEmailsOpened,
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
        seriesAllEmailsReplied(){
            if(this.sAllEmailsReplied.length > 0){
                return this.sAllEmailsReplied
            }else{
                return []
            }
        },
        chartOptionsAllEmailsReplied(){
            if(this.labelAllEmailsReplied.length > 0){
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
                            labels: this.labelAllEmailsReplied,
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
        seriesAllEmailsRetry(){
            if(this.sAllEmailsRetry.length > 0){
                return this.sAllEmailsRetry
            }else{
                return []
            }
        },
        chartOptionsAllEmailsRetry(){
            if(this.labelAllEmailsRetry.length > 0){
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
                            labels: this.labelAllEmailsRetry,
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
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        getGraphData(){
            this.getEmailAllData()
        },
        getEmailAllData(){
            this.loader = false
            this.labelAllEmails = []
            this.sAllEmails = []
            this.labelAllEmailsDelivered = []
            this.sAllEmailsDelivered = []
            this.labelAllEmailsBounced = []
            this.sAllEmailsBounced = []
            this.labelAllEmailsClicked = []
            this.sAllEmailsClicked = []
            this.labelAllEmailsOpened = []
            this.sAllEmailsReplied = []
            this.labelAllEmailsReplied = []
            this.sAllEmailsRetry = []
            this.labelAllEmailsRetry = []
            
            this.sAllEmailsOpened = []
            
            this.agentName = []
            // this.form.dateRange= {}
            // this.form.agentName = null
            
            this.form.post("/api/get-emails-all-data").then((response) => 
            {
                this.labelAllEmails = response.data.label
                this.sAllEmails = response.data.series
                this.labelAllEmailsDelivered = response.data.labelDelivered
                this.sAllEmailsDelivered = response.data.seriesDelivered
                this.labelAllEmailsBounced = response.data.labelBounced
                this.sAllEmailsBounced = response.data.seriesBounced
                this.labelAllEmailsClicked = response.data.labelClicked
                this.sAllEmailsClicked = response.data.seriesClicked
                this.labelAllEmailsOpened = response.data.labelOpened
                this.sAllEmailsOpened = response.data.seriesOpened
                this.labelAllEmailsReplied = response.data.labelReplied
                this.sAllEmailsReplied = response.data.seriesReplied
                this.labelAllEmailsRetry = response.data.labelRetry
                this.sAllEmailsRetry = response.data.seriesRetry
                this.form.dateRange = {startDate : new Date(response.data.startDate), endDate:new Date(response.data.endDate)}
                this.agentName = response.data.agentName
                this.loader = false
            })
        },
        dataPointSelectionHandlerAgentLoginTime(e, chartContext, config) {
           
            this.form.agentName = this.agentName[config.dataPointIndex]
            this.loader = false
            // this.labelAllEmails = []
            // this.sAllEmails = []
            this.labelAllEmailsDelivered = []
            this.sAllEmailsDelivered = []
            this.labelAllEmailsBounced = []
            this.sAllEmailsBounced = []
            this.labelAllEmailsClicked = []
            this.sAllEmailsClicked = []
            this.labelAllEmailsOpened = []
            this.sAllEmailsReplied = []
            this.labelAllEmailsReplied = []
            this.sAllEmailsRetry = []
            this.labelAllEmailsRetry = []
            
            this.sAllEmailsOpened = []
            
            this.form.post("/api/get-emails-all-data").then((response) => 
            {
                // this.labelAllEmails = response.data.label
                // this.sAllEmails = response.data.series
                this.labelAllEmailsDelivered = response.data.labelDelivered
                this.sAllEmailsDelivered = response.data.seriesDelivered
                this.labelAllEmailsBounced = response.data.labelBounced
                this.sAllEmailsBounced = response.data.seriesBounced
                this.labelAllEmailsClicked = response.data.labelClicked
                this.sAllEmailsClicked = response.data.seriesClicked
                this.labelAllEmailsOpened = response.data.labelOpened
                this.sAllEmailsOpened = response.data.seriesOpened
                this.labelAllEmailsReplied = response.data.labelReplied
                this.sAllEmailsReplied = response.data.seriesReplied
                //this.form.dateRange = {startDate : new Date(response.data.startDate), endDate:new Date(response.data.endDate)}
                this.agentName = response.data.agentName
                this.loader = false
            })
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