<template>
    <div>
        <div>
            <div class="table-responsive border-bottom">
               <div class="synops">          
                    <div class="inner-synop cursor-pointer">
                        <h4  class="number">250</h4>
                        <p class="text-success">Total Calls</p>
                    </div>
                    <div class="inner-synop cursor-pointer">
                        <h4  class="number" >50 
                            <small>(20%)</small> 
                        </h4>
                        <p>Agent Name</p>
                    </div>
                    <div class="inner-synop cursor-pointer">
                        <h4  class="number" >50 
                            <small>(20%)</small> 
                        </h4>
                        <p>Agent Name</p>
                    </div>
                    <div class="inner-synop cursor-pointer">
                        <h4  class="number" >50 
                            <small>(20%)</small> 
                        </h4>
                        <p>Agent Name</p>
                    </div>
                    <div class="inner-synop cursor-pointer">
                        <h4  class="number" >50 
                            <small>(20%)</small> 
                        </h4>
                        <p>Agent Name</p>
                    </div>
                    <div class="inner-synop cursor-pointer">
                        <h4  class="number" >50 
                            <small>(20%)</small> 
                        </h4>
                        <p>Agent Name</p>
                    </div>
               </div>
            </div>
            <div class="filterbox p-2">
                <div class="row m-0">
                    <div class="col-md-2 col-6">
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
            </div>
            <div class="calls-chart-div border-top pt-2">
                <div class="row m-0">
                    <div class="col-md-4 col lg-3 col-sm-6 col-12 p-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="text-capitalize m-0">
                                    Agent Call Status
                                </h5>
                            </div>
                            <div class="card-body">
                                Pie Chart
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col lg-3 col-sm-6 col-12 p-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="text-capitalize m-0">
                                    Highlights
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Most efficient Agent</li>
                                    <li>Call success rate</li>
                                    <li>DNC rate</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col lg-3 col-sm-6 col-12 p-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="text-capitalize m-0">
                                    Dispositions Status
                                </h5>
                            </div>
                            <div class="card-body">
                                Pie Chart
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col lg-3 col-sm-6 col-12 p-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="text-capitalize m-0">
                                    Agent Based Talktime
                                </h5>
                            </div>
                            <div class="card-body">
                                Pie Chart
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col lg-3 col-sm-6 col-12 p-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="text-capitalize m-0">
                                    Agent Based ACW
                                </h5>
                            </div>
                            <div class="card-body">
                                Pie Chart
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col lg-3 col-sm-6 col-12 p-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="text-capitalize m-0">
                                    Agent Based Waiting Time
                                </h5>
                            </div>
                            <div class="card-body">
                                Pie Chart
                            </div>
                        </div>
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

export default {
    components:{DateRangePicker},
    data() {
        return {
            loader:false,
            records:{},
            form: new Form({
                dateRange:{},
            }),
            loader_url: '/img/spinner.gif',
        }
    },
    filters: {
           },
    computed: {
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
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
    },
    created() {
        
    }
}
</script>