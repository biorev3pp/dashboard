<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-4 col-12 pl-0">
                    <date-range-picker ref="picker" :locale-data="{ firstDay: 1, format: 'dd-mm-yyyy HH:mm:ss' }" :timePicker24Hour="false" :showWeekNumbers="true" :showDropdowns="true"  :dateFormat="dateFormat" :autoApply="false" v-model="form.dateRange" @update="getRecords">
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
                    <toggle-button v-model="showcpercent" :margin="3" :width="110" :height="28" :labels="{checked: 'Percent', unchecked: 'Count'}" :switch-color="{checked: '#800080', unchecked: '#27408B'}" :color="{checked: '#E599E5', unchecked: '#4E9FFE'}" />
                </div>
            </div>
        </div>
        <div class="mapping-div syn-report">
            <div class="row m-0">
                <div class="col-md-12 my-3">
                    <div class="card mb-3">
                        <div class="card-header">
                            <b class="card-title m-0 text-uppercase"> Prospect Sync Overview</b>
                            <i class="bi bi-plus-square  float-right" @click="pshow = false" v-if="pshow"></i>
                            <i class="bi bi-dash-square float-right" @click="pshow = true" v-else></i>
                        </div> 
                        <div class="card-body" :class="[(pshow)?'height-0':'']">
                            <div class="synops border-bottom">          
                                <div class="inner-synop cursor-pointer" @click="gotoPage('contact', 1)">
                                    <h4 class="number"> {{ totalProspectCount | freeNumber }} </h4>
                                    <p>Total Prospects</p>
                                </div>
                                <div class="inner-synop cursor-pointer" @click="gotoPage('contact', 2)">
                                    <h4 class="number">
                                        <span v-if="showcpercent"> {{ tncpercent }}</span>
                                        <span v-else> {{ totalNewProspectCount | freeNumber }} </span> 
                                    </h4>
                                    <p>New Prospects</p>
                                </div>
                                <div class="inner-synop cursor-pointer" @click="gotoPage('contact', 3)">
                                    <h4 class="number">
                                        <span v-if="showcpercent"> {{ tsgpercent }}</span>
                                        <span v-else> {{ totalStageUpdateCount | freeNumber }} </span>     
                                    </h4>
                                    <p>Stage Update</p>
                                </div>
                                <div class="inner-synop cursor-pointer" @click="gotoPage('contact', 4)">
                                    <h4 class="number">
                                         <span v-if="showcpercent"> {{ tcnpercent }}</span>
                                        <span v-else> {{ totalContactNoUpdateCount | freeNumber }} </span>
                                    </h4>
                                    <p>Contact No Update</p>
                                </div>
                                <div class="inner-synop cursor-pointer" @click="gotoPage('contact', 5)">
                                    <h4 class="number">
                                         <span v-if="showcpercent"> {{ cfupercent }}</span>
                                        <span v-else> {{ totalCustomFieldUpdateCount | freeNumber }} </span>
                                    </h4>
                                    <p>Custom Field Update</p>
                                </div>
                            </div>
                             <v-frappe-chart v-if="cdata.length > 0" type="bar" :labels="elabels"
                                :data="pdata"
                                :barOptions="{ stacked: 1 }"
                                :colors="['#b8daff', '#007bff', '#ffeeba', '#FFC106', '#c3e6cb']" 
                                :truncateLegends="true"
                                :axisOptions="{
                                    xAxisMode : span ,
                                    xIsSeries : true
                                }"
                            />
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header">
                            <b class="card-title m-0 text-uppercase mr-2"> Outreach Email Sync Overview</b>
                            <button class="btn btn-sm py-1 mr-1 btn-toggler" type="button" :class="[(edtype == 'all')?'btn-primary':'btn-outline-primary']" @click="changeEmailType('all')">All</button>
                            <button class="btn btn-sm py-1 mr-1 btn-toggler" type="button" :class="[(edtype == 'single')?'btn-primary':'btn-outline-primary']" @click="changeEmailType('single')">One Off</button>
                            <button class="btn btn-sm py-1 btn-toggler" type="button" :class="[(edtype == 'sequence')?'btn-primary':'btn-outline-primary']" @click="changeEmailType('sequence')">Sequence</button>
                            <i class="bi bi-plus-square  float-right" @click="eshow = false" v-if="eshow"></i>
                            <i class="bi bi-dash-square float-right" @click="eshow = true" v-else></i>
                        </div> 
                        <div class="card-body" :class="[(eshow)?'height-0':'']">
                            <div class="synops border-bottom">          
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('email', 1)">
                                    <h4 class="number">
                                        <span v-if="edtype == 'all'">
                                            {{ (totalEmailDeliveredCounter+totalSEmailDeliveredCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailDeliveredCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailDeliveredCounter) | freeNumber }}
                                        </span>
                                    </h4>
                                    <p>Total Delivered</p>
                                </div>
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('email', 2)">
                                    <h4 class="number" v-if="showcpercent">
                                        <span v-if="edtype == 'all'">
                                            {{ ((totalEmailOpenedCounter+totalSEmailOpenedCounter)/(totalEmailDeliveredCounter+totalSEmailDeliveredCounter))*100 | freeNumber }}%
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailOpenedCounter/totalEmailDeliveredCounter)*100 | freeNumber }}%
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailOpenedCounter/totalSEmailDeliveredCounter)*100 | freeNumber }}%
                                        </span>
                                    </h4>
                                    <h4 class="number" v-else>
                                        <span v-if="edtype == 'all'">
                                            {{ (totalEmailOpenedCounter+totalSEmailOpenedCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailOpenedCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailOpenedCounter) | freeNumber }}
                                        </span>
                                    </h4>
                                    <p>Total Opened</p>
                                </div>
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('email', 3)">
                                    <h4 class="number" v-if="showcpercent">
                                        <span v-if="edtype == 'all'">
                                            {{ ((totalEmailClickedCounter+totalSEmailClickedCounter)/(totalEmailDeliveredCounter+totalSEmailDeliveredCounter))*100 | freeNumber }}%
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailClickedCounter/totalEmailDeliveredCounter)*100 | freeNumber }}%
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailClickedCounter/totalSEmailDeliveredCounter)*100 | freeNumber }}%
                                        </span>
                                    </h4>
                                    <h4 class="number" v-else>
                                        <span v-if="edtype == 'all'">
                                            {{ (totalEmailClickedCounter+totalSEmailClickedCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailClickedCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailClickedCounter) | freeNumber }}
                                        </span>
                                    </h4>
                                    <p>Total Clicked</p>
                                </div>
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('email', 4)">
                                    <h4 class="number" v-if="showcpercent">
                                        <span v-if="edtype == 'all'">
                                            {{ ((totalEmailRepliedCounter+totalSEmailRepliedCounter)/(totalEmailDeliveredCounter+totalSEmailDeliveredCounter))*100 | freeNumber }}%
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailRepliedCounter/totalEmailDeliveredCounter)*100 | freeNumber }}%
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailRepliedCounter/totalSEmailDeliveredCounter)*100 | freeNumber }}%
                                        </span>
                                    </h4>
                                    <h4 class="number" v-else>
                                        <span v-if="edtype == 'all'">
                                            {{ (totalEmailRepliedCounter+totalSEmailRepliedCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailRepliedCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailRepliedCounter) | freeNumber }}
                                        </span>
                                    </h4>
                                    <p>Total Replied</p>
                                </div>
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('email', 5)">
                                    <h4 class="number" v-if="showcpercent">
                                        <span v-if="edtype == 'all'">
                                            {{ ((totalEmailBouncedCounter+totalSEmailBouncedCounter)/(totalEmailDeliveredCounter+totalSEmailDeliveredCounter))*100 | freeNumber }}%
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailBouncedCounter/totalEmailDeliveredCounter)*100 | freeNumber }}%
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailBouncedCounter/totalSEmailDeliveredCounter)*100 | freeNumber }}%
                                        </span>
                                    </h4>
                                    <h4 class="number" v-else>
                                        <span v-if="edtype == 'all'">
                                            {{ (totalEmailBouncedCounter+totalSEmailBouncedCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'single'">
                                            {{ (totalEmailBouncedCounter) | freeNumber }}
                                        </span>
                                        <span v-if="edtype == 'sequence'">
                                            {{ (totalSEmailBouncedCounter) | freeNumber }}
                                        </span>
                                    </h4>
                                    <p>Total Bounced</p>
                                </div>
                                
                            </div>
                            <v-frappe-chart v-if="cdata.length > 0" type="bar" :labels="elabels"
                                :data="edata"
                                :barOptions="{ stacked: 1 }"
                                :colors="['#b8daff', '#ffc107', '#007bff', '#28a745', '#dc3545']"
                                :truncateLegends="true"
                                :axisOptions="{
                                    xAxisMode : span ,
                                    xIsSeries : true
                                }"
                            />
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header hide">
                            <b class="card-title m-0 text-uppercase mr-2"> Five9 Calls Sync Overview</b>
                            <i class="bi bi-plus-square  float-right" @click="cshow = false" v-if="cshow"></i>
                            <i class="bi bi-dash-square float-right" @click="cshow = true" v-else></i>
                        </div> 
                        <div class="card-body" :class="[(cshow)?'height-0':'']">
                            <div class="synops border-bottom">
                                <div class="inner-synop cursor-pointer" @click="gotoPage('call', 1)">
                                    <h4 class="number">{{ totalcall | freeNumber }} </h4>
                                    <p>Total Calls</p>
                                </div>
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('call', 2)">
                                    <h4 class="number">
                                        <span v-if="showcpercent"> {{ mcrpercent }}</span>
                                        <span v-else> {{ mcrtotal | freeNumber }} </span> 
                                    </h4>
                                    <p>Answered - Mobile Phone</p>
                                </div>
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('call', 3)">
                                    <h4 class="number">
                                        <span v-if="showcpercent"> {{ hcrpercent }}</span>
                                        <span v-else> {{ hcrtotal | freeNumber }}  </span> 
                                    </h4>
                                    <p>Answered - Home Phone</p>
                                </div>
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('call', 4)">
                                    <h4 class="number">
                                        <span v-if="showcpercent"> {{ wcrpercent }}</span>
                                        <span v-else> {{ wcrtotal | freeNumber }}  </span> 
                                    </h4>
                                    <p>Answered - Work Phone</p>
                                </div>
                                <div class="inner-synop  cursor-pointer" @click="gotoPage('call', 5)">
                                    <h4 class="number">
                                        <span v-if="showcpercent"> {{ napercent }}</span>
                                        <span v-else> {{ not_answered | freeNumber }} </span> 
                                    </h4>
                                    <p>Not Answered</p>
                                </div>
                            </div>
                            
                            <v-frappe-chart v-if="cdata.length > 0" type="bar" :labels="elabels"
                                :data="cdata"
                                :barOptions="{ stacked: 1 }"
                                :colors="['#6FFF6F', '#60B1FF', '#B8DC6F', '#EA9090']"
                                :truncateLegends="true"
                                :axisOptions="{
                                    xAxisMode : span ,
                                    xIsSeries : true
                                }"
                            />
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

import DateRangePicker from 'vue2-daterange-picker';
import { VFrappeChart } from 'vue-frappe-chart'
import { ToggleButton } from 'vue-js-toggle-button'

export default {
    components: { DateRangePicker, VFrappeChart, ToggleButton },
    data() {
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            pshow:false,
            eshow:false,
            cshow:false,
            showcpercent:false,
            dates : [],
            edtype:'all',
            emailData : [],
            emailOData : [],
            emailSData : [],
            callData: [],
            contactData: [],
            //call
            totalcall : 0,
            not_answered : 0,
            mcrtotal : 0,
            hcrtotal : 0,
            wcrtotal : 0,
            //email
            totalEmailDeliveredCounter : 0,
            totalEmailOpenedCounter : 0,
            totalEmailClickedCounter : 0,
            totalEmailRepliedCounter : 0,
            totalEmailBouncedCounter : 0,

            totalSEmailDeliveredCounter : 0,
            totalSEmailOpenedCounter : 0,
            totalSEmailClickedCounter : 0,
            totalSEmailRepliedCounter : 0,
            totalSEmailBouncedCounter : 0,
            //contact
            totalProspectCount : 0,
            totalNewProspectCount : 0,
            totalStageUpdateCount : 0,
            totalContactNoUpdateCount : 0,
            totalCustomFieldUpdateCount : 0,
            

            form: new Form({
                dateRange : {},
            }),
        }
    },
    filters: {
    },
    computed: {
        tncpercent() {
            let a = (this.totalNewProspectCount/this.totalProspectCount)*100;
            a = a.toFixed(0);
            return a+'%';
        },
        tsgpercent() {
            let a = (this.totalStageUpdateCount/this.totalProspectCount)*100;
            a = a.toFixed(0);
            return a+'%';
        },
        tcnpercent() {
            let a = (this.totalContactNoUpdateCount/this.totalProspectCount)*100;
            a = a.toFixed(0);
            return a+'%';
        },
        cfupercent() {
            let a = (this.totalCustomFieldUpdateCount/this.totalProspectCount)*100;
            a = a.toFixed(0);
            return a+'%';
        },
        mcrpercent() {
            let mcr = (this.mcrtotal/this.totalcall)*100;
            mcr = mcr.toFixed(0);
            return mcr+'%'; 
        },
        hcrpercent() {
            let hcr = (this.hcrtotal/this.totalcall)*100;
            hcr = hcr.toFixed(0);
            return hcr+'%'; 
        },
        wcrpercent() {
            let wcr = (this.wcrtotal/this.totalcall)*100;
            wcr = wcr.toFixed(0);
            return wcr+'%'; 
        },
        napercent() {
            let nap = (this.not_answered/this.totalcall)*100;
            nap = nap.toFixed(0);
            return nap+'%'; 
        },
        elabels() {
            if(this.dates) {
                return this.dates;
            }  else {
               return [];
            }   
        },
        edata() {
            if(this.edtype == 'all') { return this.emailData; }
            else if(this.edtype == 'single') { return this.emailOData; }
            else if(this.edtype == 'sequence') { return this.emailSData; }
            else { return []; }   
        },
        cdata() {
            if(this.callData) {
                return this.callData;
            }  else {
               return [];
            }   
        },
        pdata() {
            if(this.contactData) {
                return this.contactData;
            }  else {
               return [];
            }   
        },
    },
    methods: {
        changeEmailType(et) {
            this.edtype = et;
        },
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        
        resetForm() {

        },
        getRecords() {
            this.$Progress.start()
            if(this.form.dateRange.startDate){
                var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                var ed = endDate.split("T")
                var sd = startDate.split("T")
                var sdate = sd[0]
                var edate = ed[0]
            }else {
                var today = new Date();
                var dd = today.getDate();

                var mm = today.getMonth()+1; 
                var yyyy = today.getFullYear();

                dd = dd < 10 ? '0'+ dd : dd;
                mm = mm < 10 ?  '0'+ mm : mm;
                
                var edate = yyyy+'/'+mm+'/'+dd

                var date = new Date();
                var last = new Date(date.getTime() - (7 * 24 * 60 * 60 * 1000));
                var ldd = last.getDate()
                ldd = ldd < 10 ? '0' + ldd : ldd
                var lmm = last.getMonth()+1
                lmm = lmm < 10 ? '0' + lmm : lmm
                var sdate = yyyy+'/'+lmm+'/'+ldd
            }
            let $this = this;
            axios.get('/api/get-report-records?sdate=' +sdate + '&edate=' + edate)
            .then((response) => {
                $this.dates = response.data.dates;
                $this.emailData = response.data.emailData;
                $this.emailOData = response.data.emailOData;
                $this.emailSData = response.data.emailSData;
                $this.callData = response.data.callData;
                $this.contactData = response.data.contactData;
                $this.totalcall = response.data.mcrtotal+response.data.hcrtotal+response.data.wcrtotal+response.data.notAnsweredTotal
                $this.not_answered = response.data.notAnsweredTotal
                $this.mcrtotal = response.data.mcrtotal
                $this.hcrtotal = response.data.hcrtotal
                $this.wcrtotal = response.data.wcrtotal
                
                $this.totalProspectCount = response.data.totalProspectCount
                $this.totalNewProspectCount = response.data.totalNewProspectCount
                $this.totalStageUpdateCount = response.data.totalStageUpdateCount
                $this.totalContactNoUpdateCount = response.data.totalContactNoUpdateCount
                $this.totalCustomFieldUpdateCount = response.data.totalCustomFieldUpdateCount

                $this.totalEmailDeliveredCounter = response.data.totalEmailDeliveredCounter
                $this.totalEmailOpenedCounter = response.data.totalEmailOpenedCounter
                $this.totalEmailClickedCounter = response.data.totalEmailClickedCounter
                $this.totalEmailRepliedCounter = response.data.totalEmailRepliedCounter
                $this.totalEmailBouncedCounter = response.data.totalEmailBouncedCounter

                $this.totalSEmailDeliveredCounter = response.data.totalSEmailDeliveredCounter
                $this.totalSEmailOpenedCounter = response.data.totalSEmailOpenedCounter
                $this.totalSEmailClickedCounter = response.data.totalSEmailClickedCounter
                $this.totalSEmailRepliedCounter = response.data.totalSEmailRepliedCounter
                $this.totalSEmailBouncedCounter = response.data.totalSEmailBouncedCounter
                this.$Progress.finish()
            });
        },
        gotoPage(typ, val) {
            if(this.form.dateRange.startDate){
                var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                var ed = endDate.split("T")
                var sd = startDate.split("T")
                var sdate = sd[0]
                var edate = ed[0]
            }else {
                var today = new Date();
                var dd = today.getDate();

                var mm = today.getMonth()+1; 
                var yyyy = today.getFullYear();

                dd = dd < 10 ? '0'+ dd : dd;
                mm = mm < 10 ?  '0'+ mm : mm;
                
                var edate = yyyy+'-'+mm+'-'+dd

                var date = new Date();
                var last = new Date(date.getTime() - (7 * 24 * 60 * 60 * 1000));
                var ldd = last.getDate()
                ldd = ldd < 10 ? '0' + ldd : ldd
                var lmm = last.getMonth()+1
                lmm = lmm < 10 ? '0' + lmm : lmm
                var sdate = yyyy+'-'+lmm+'-'+ldd
            }
            if(typ == 'email') {
                typ = typ+'-'+this.edtype;
            }
            let routeData = this.$router.resolve({path: '/log/sync-log-details?t='+typ+'&v='+val+'&s='+sdate+'&e='+edate});
            window.open(routeData.href, '_blank');
        }
    },
    created(){
        },
    beforeMount() {
        
        },
    mounted() {
        this.getRecords();
    }
}
</script>